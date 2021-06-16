<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Poll;
use App\Form\PollType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\GradeCreate;
use MjOpenApi\Model\PollCreate;
use MjOpenApi\Model\ProposalCreate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;


/** @noinspection PhpUnused */
/**
 * Submitting a new poll when not logged will automatically create a new temporary user.
 *
 * @Route("/new-poll", name="create_poll")
 * @Route("/new-poll.html", name="create_poll_html")
 *
 * Class CreatePollController
 * @package App\Controller
 */
final class CreatePollController extends AbstractController
{

    use Has\ApiAccess;
    use Has\Translator;
    use Has\UserSession;

    /**
     * @param Request $request
     * @param GuardAuthenticatorHandler $guard
     * @return Response
     */
    public function __invoke(
        Request $request,
        GuardAuthenticatorHandler $guard
    ): Response {

        $amountOfProposalsToAddWhenRequested = PollType::AMOUNT_OF_PROPOSALS_TO_ADD;

        $pollApi = $this->getApiFactory()->getPollApi();

        $queryPoll = $request->query->get('poll'); // autofill, low-priority
        $sentPoll = $request->request->get('poll');

        $amountOfProposals = PollType::DEFAULT_AMOUNT_OF_PROPOSALS;

        $poll = new Poll();
        if (null !== $queryPoll && \is_array($queryPoll)) {
            $poll->setSubject($queryPoll['subject'] ?? '');

        }
        if (null !== $sentPoll && \is_array($sentPoll)) {
            $amountOfProposals = $this->sanitizeAmountOfProposals($sentPoll[PollType::OPTION_AMOUNT_OF_PROPOSALS] ?? $amountOfProposals);
        }
        $poll->setAmountOfProposals($amountOfProposals);

        $options = [
            PollType::OPTION_AMOUNT_OF_GRADES => PollType::DEFAULT_AMOUNT_OF_GRADES,
            PollType::OPTION_AMOUNT_OF_PROPOSALS => $amountOfProposals,
        ];

        /** @var Form $form */
        $form = $this->createForm(PollType::class, $poll, $options);
        $form->handleRequest($request);

        // Do it a second time with the correct data in $poll
        // This is not pretty, but it works.  Fix if you can !
        $form = $this->createForm(PollType::class, $poll, $options);
        $form->handleRequest($request);

        $shouldSend = $form->isSubmitted() && $form->isValid();

        // The user requested more proposals, don't send the form
        if ($form->getClickedButton() === $form->get('moreProposals')){
            $options[PollType::OPTION_AMOUNT_OF_PROPOSALS] = $this->sanitizeAmountOfProposals(
                $options[PollType::OPTION_AMOUNT_OF_PROPOSALS]
                +
                $amountOfProposalsToAddWhenRequested
            );

            $poll->setAmountOfProposals($options[PollType::OPTION_AMOUNT_OF_PROPOSALS]);

            // Rebuild the whole form because we can't change options after its initial build
            $form = $this->createForm(PollType::class, $poll, $options);

            $form->clearErrors();
            $shouldSend = false;
        }

        if ($shouldSend) {

            if ( ! $this->getUserSession()->isLogged()) {
                $registered = $this->quickRegister($request, $guard);
                if (true !== $registered) {
                    return $registered;
                }
            }

            // $form->getData() holds the submitted values
            //$poll = $form->getData();
            // but, the original `$poll` variable has also been updated

            $pollCreate = new PollCreate();
            $pollCreate->setSubject($poll->getSubject());
            $pollCreate->setScope($poll->getScope());

            $proposalCreates = [];
            foreach ($poll->getProposals() as $proposalTitle) {
                if (empty($proposalTitle)) {
                    continue;
                }
                
                $proposalCreate = new ProposalCreate();
                //$proposalCreate->setPoll($pollCreate);  // DO NOT ENABLE THIS, IT WILL BLOW UP
                $proposalCreate->setTitle($proposalTitle);

                $proposalCreates[] = $proposalCreate;
            }
            $pollCreate->setProposals($proposalCreates);

            $gradingPreset = $poll->getGradingPreset();
            if ('custom' === $gradingPreset) {
                // TODO : support custom, user-defined grades
            } else {
                $amountOfGrades = 6;

                $gradeCreates = [];
                for ($i=0 ; $i<$amountOfGrades ; $i++) {
                    $gradeCreate = new GradeCreate();
                    $gradeCreate->setLevel($i);
                    $gradeCreate->setName($this->trans(
                        "${gradingPreset}.grades.${i}", [], 'grades'
                    ));

                    $gradeCreates[] = $gradeCreate;
                }
                $pollCreate->setGrades($gradeCreates);
            }

            $failed = false;
            $response = null;
            try {
                $response = $pollApi->postPollCollection($pollCreate);
            } catch (ApiException $api_exception) {
                $failed = true;
                $this->getApiExceptionAdapter()->setFormErrorsIfAny($form, $api_exception);
                if ($form->isValid()) {
                    $message = $this->getApiExceptionAdapter()->toString($api_exception);
                    $this->addFlash("error", $message);
                }
            }

            if ( ! $failed) {
                if (null === $response) {
                    trigger_error("API response undefined.", E_USER_ERROR);
                    $this->addFlash("error", "API response is empty");
                    return $this->render('poll/create.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                return $this->redirectToRoute('read_poll_html', [
                    'pollId' => $response->getUuid(),
                ]);
            }
        }

        return $this->render('poll/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function sanitizeAmountOfProposals($amount)
    {
        return clamp(
            PollType::MINIMUM_AMOUNT_OF_PROPOSALS,
            PollType::MAXIMUM_AMOUNT_OF_PROPOSALS,
            (int) $amount
        );
    }
}

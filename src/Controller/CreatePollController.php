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


/** @noinspection PhpUnused */
/**
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

    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $pollApi = $this->getApiFactory()->getPollApi();

        $sentPoll = $request->get('poll');

        $amountOfProposals = PollType::DEFAULT_AMOUNT_OF_PROPOSALS;

        $poll = new Poll();
        if (null !== $sentPoll && \is_array($sentPoll)) {
            $poll->setScope($sentPoll['scope'] ?? 'unlisted');
            $poll->setSubject($sentPoll['subject'] ?? '');
            $amountOfProposals = $this->sanitizeAmountOfProposals($sentPoll[PollType::OPTION_AMOUNT_OF_PROPOSALS] ?? $amountOfProposals);
        }
        $poll->setAmountOfProposals($amountOfProposals);

//        dd($request);

        $options = [
            PollType::OPTION_AMOUNT_OF_GRADES => PollType::DEFAULT_AMOUNT_OF_GRADES,
            PollType::OPTION_AMOUNT_OF_PROPOSALS => $amountOfProposals,
        ];

        /** @var Form $form */
        $form = $this->createForm(PollType::class, $poll, $options);
        $form->handleRequest($request);

        $shouldSend = $form->isSubmitted() && $form->isValid();

        if ($form->getClickedButton() === $form->get('moreProposals')){
            // add more proposals
            $options[PollType::OPTION_AMOUNT_OF_PROPOSALS] = $this->sanitizeAmountOfProposals(
                $options[PollType::OPTION_AMOUNT_OF_PROPOSALS] + 5
            );
            $poll->setAmountOfProposals($options[PollType::OPTION_AMOUNT_OF_PROPOSALS]);
            $request->request->remove(PollType::OPTION_AMOUNT_OF_PROPOSALS);
            $request->query->remove(PollType::OPTION_AMOUNT_OF_PROPOSALS);
            $request->attributes->remove(PollType::OPTION_AMOUNT_OF_PROPOSALS);
            // REBUILD THE WHOLE FORM NOOo
            /** @var Form $form */
            $form = $this->createForm(PollType::class, $poll, $options);
//            $form->handleRequest($request);
            //////////////////////////////

            $form->clearErrors();
            $shouldSend = false;
        }


        if ($shouldSend) {
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

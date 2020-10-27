<?php

namespace App\Controller;

use App\Adapter\ApiExceptionAdapter;
use App\Entity\Poll;
use App\Factory\ApiFactory;
use App\Form\PollType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\PollCreate;
use MjOpenApi\Model\ProposalCreate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreatePollController extends AbstractController
{
    /**
     * @Route("/new-poll", name="create_poll")
     * @Route("/new-poll.html", name="create_poll_html")
     *
     * @param Request $request
     * @param ApiExceptionAdapter $exceptionAdapter
     * @param ApiFactory $apiFactory
     * @return Response
     */
    public function index(
        Request $request,
        ApiExceptionAdapter $exceptionAdapter,
        ApiFactory $apiFactory
    ): Response
    {
        $pollApi = $apiFactory->getPollApi();

        $poll = new Poll();
        $poll->setScope($request->get('scope', 'unlisted'));
        $poll->setSubject($request->get('subject', ''));

        $options = [
            PollType::OPTION_AMOUNT_OF_GRADES => PollType::DEFAULT_AMOUNT_OF_GRADES,
            PollType::OPTION_AMOUNT_OF_PROPOSALS => PollType::DEFAULT_AMOUNT_OF_PROPOSALS,
        ];

        /** @var Form $form */
        $form = $this->createForm(PollType::class, $poll, $options);
        $form->handleRequest($request);

        $shouldSubmit = $form->isSubmitted() && $form->isValid();

        if ($form->getClickedButton() === $form->get('moreProposals')){
            // add more proposals
            $options[PollType::OPTION_AMOUNT_OF_PROPOSALS] = $options[PollType::OPTION_AMOUNT_OF_PROPOSALS] + 5;

            // REBUILD THE WHOLE FORM NOOo
            /** @var Form $form */
            $form = $this->createForm(PollType::class, $poll, $options);
            $form->handleRequest($request);
            //////////////////////////////

            $form->clearErrors();
            $shouldSubmit = false;
        }


        if ($shouldSubmit) {
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



            $failed = false;
            try {
                $pollApi->postPollCollection($pollCreate);
            } catch (ApiException $api_exception) {
                $failed = true;
                $exceptionAdapter->setFormErrorsIfAny($form, $api_exception);
                if ($form->isValid()) {
                    $message = $exceptionAdapter->toString($api_exception);
                    $this->addFlash("error", $message);
                }
            }

            if ( ! $failed) {
                return $this->redirectToRoute('read_poll_html', ['pollId'=>"okokok"]);
            }
        }


        return $this->render('poll/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Adapter\ApiExceptionAdapter;
use App\Entity\Poll;
use App\Factory\ApiFactory;
use App\Form\PollType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\PollCreate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $form = $this->createForm(PollType::class, $poll);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            //$poll = $form->getData();
            // but, the original `$poll` variable has also been updated

            $pollCreate = new PollCreate();
            $pollCreate->setSubject($poll->getSubject());
            $pollCreate->setScope($poll->getScope());

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

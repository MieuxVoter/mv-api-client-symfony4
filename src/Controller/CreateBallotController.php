<?php

namespace App\Controller;

use App\Adapter\ApiExceptionAdapter;
use App\Entity\Ballot;
use App\Factory\ApiFactory;
use App\Form\BallotType;
use App\Form\PollType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\BallotCreate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;


/**
 * @Route(
 *     path="/polls/{pollId}-participate.html",
 *     name="create_ballot_html",
 *     requirements={"pollId"="[^.]+"},
 * )
 */
class CreateBallotController extends AbstractController
{


    public function __invoke(
        string $pollId,
        Request $request,
        ApiExceptionAdapter $exceptionAdapter,
        ApiFactory $apiFactory
    )
    {
        /// REFACTOR ME ///
        $apiInstance = $apiFactory->getPollApi();
        $pollRead = null;
        try {
            $pollRead = $apiInstance->getPollItem($pollId);
        } catch (ApiException $e) {
            if (Response::HTTP_NOT_FOUND == $e->getCode()) {
                throw new NotFoundHttpException("No poll found.");
            }
            throw $e;
        }
        ///////////////////

        $ballot = new Ballot();

        $options = [
            'grades' => $pollRead->getGrades(),
            'proposals' => $pollRead->getProposals(),
        ];

        /** @var Form $form */
        $form = $this->createForm(BallotType::class, $ballot, $options);
        $form->handleRequest($request);


        $showFormResponse = $this->render('poll/participate.html.twig', [
            'poll' => $pollRead,
            'form' => $form->createView(),
        ]);


        $shouldSend = $form->isSubmitted() && $form->isValid();

        // …
        if ($shouldSend) {
            $ballotApi = $apiFactory->getBallotApi();

            $proposals = $pollRead->getProposals();
            $judgments = $ballot->getJudgments();
            $i = 0;

            $proposal = $proposals[$i];

            $ballotCreate = new BallotCreate();
//            $ballotCreate->setProposal(
//                sprintf("/proposals/%s", $proposal->getUuid())
//            );
            $ballotCreate->setGrade(
                sprintf("/grades/%s", $judgments[$proposal->getUuid()])
            );

            $ballotResponse = null;
            try {
                $ballotResponse = $ballotApi->postBallotCollection(
//                    sprintf("/Poll/%s", $pollRead->getModelName(), $pollRead->getUuid()),
//                    sprintf("/Proposal/%s", $proposal->getModelName(), $proposal->getUuid()),
                    $pollRead->getUuid(),
                    $proposal->getUuid(),
                    $ballotCreate
                );
            } catch (ApiException $e) {
                //dd($e);
                return $exceptionAdapter->respond($e, $showFormResponse);
            }

            dd($ballotResponse);

        }

        return $showFormResponse;
    }
}

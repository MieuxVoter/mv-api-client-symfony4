<?php

declare(strict_types=1);

namespace App\Controller;

use App\Adapter\ApiExceptionAdapter;
use App\Entity\Ballot;
use App\Factory\ApiFactory;
use App\Form\BallotType;
use App\Form\PollType;
use MvApi\ApiException;
use MvApi\Model\BallotCreate;
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
 *     path="/polls/{pollId}/participate.html",
 *     name="create_ballot_html",
 *     requirements={"pollId"="[^.]+"},
 * )
 */
final class CreateBallotController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(
        string $pollId,
        Request $request
    )
    {
        $apiInstance = $this->getApiFactory()->getPollApi();
        $pollRead = null;
        try {
            $pollRead = $apiInstance->getPollItem($pollId);
        } catch (ApiException $e) {
            //$this->addStrike(Strike::WARNING, $e, $request?);
            return $this->renderApiException($e, $request);
        }

        $ballot = new Ballot();

        $options = [
            'grades' => $pollRead->getGrades(),
            'proposals' => $pollRead->getProposals(),
        ];

        /** @var Form $form */
        $form = $this->createForm(BallotType::class, $ballot, $options);
        $form->handleRequest($request);

        $shouldSend = $form->isSubmitted() && $form->isValid();

        // …
        if ($shouldSend) {
            $ballotApi = $this->getApiFactory()->getBallotApi();

            $proposals = $pollRead->getProposals();
            $judgments = $ballot->getJudgments();

            $ballotsCreated = [];

            foreach ($proposals as $proposal) {

                $ballotCreate = new BallotCreate();
                $ballotCreate->setGrade(
                    sprintf("/grades/%s", $judgments[$proposal->getUuid()])
                );

                $ballotResponse = null;
                try {
                    $ballotResponse = $ballotApi->postBallotCollection(
                        $pollRead->getUuid(),
                        $proposal->getUuid(),
                        $ballotCreate
                    );
                } catch (ApiException $e) {
                    $this->getApiExceptionAdapter()->setFormErrorsIfAny($form, $e);
                    $showFormResponse = $this->render('ballot/create.html.twig', [
                        'poll' => $pollRead,
                        'form' => $form->createView(),
                    ]);
                    return $showFormResponse;
                }

                $ballotsCreated[] = $ballotResponse;

            }

            return $this->redirectToRoute('confirm_ballot_created_html', [
                'pollId' => $pollId,
            ]);
        }

        return $this->render('ballot/create.html.twig', [
            'poll' => $pollRead,
            'form' => $form->createView(),
        ]);
    }
}

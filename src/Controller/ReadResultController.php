<?php

namespace App\Controller;

use App\Factory\ApiFactory;
use MjOpenApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;


/**
 * @Route(
 *     path="/results/{id}.html",
 *     name="read_result_html",
 *     requirements={"id"="[^.]+"},
 * )
 */
class ReadResultController extends AbstractController
{
    public function __invoke(
        string $id,
        ApiFactory $apiFactory
    )
    {
        $pollId = $id; // TBD: Result id? (could be kept secret?)
        $pollApi = $apiFactory->getPollApi();
        $resultApi = $apiFactory->getResultApi();

        $poll = null;
        $result = null;
        try {
            $poll = $pollApi->getPollItem($pollId);
            $result = $resultApi->getForPollResultItem($pollId);
        } catch (ApiException $e) {

            dd($e); // fixme
//            $showFormResponse = $this->render('poll/participate.html.twig', [
//                'poll' => $pollRead,
//                'form' => $form->createView(),
//            ]);
//            return $exceptionAdapter->respond($e, $showFormResponse);
        }

        $grades = [];
        foreach ($poll->getGrades() as $grade) {
            $grades['/grades/'.$grade->getUuid()] = $grade;
        }

        return $this->render('poll/result.html.twig', [
            'poll' => $poll,
            'result' => $result,
            'grades' => $grades,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Factory\ApiFactory;
use MjOpenApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;


/**
 * For now the result id is the poll id.
 * But results may be 'hidden' and have their own id.
 *
 * @Route(
 *     path="/results/{resultId}.html",
 *     name="read_result_html",
 *     requirements={"id"="[^.]+"},
 * )
 */
class ReadResultController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(string $resultId, Request $request)
    {
        $pollId = $resultId; // TBD: Result id? (could be kept secret?)
        $pollApi = $this->getApiFactory()->getPollApi();
        $resultApi = $this->getApiFactory()->getResultApi();

        $poll = null;
        $result = null;
        try {
            $poll = $pollApi->getPollItem($pollId);
            $result = $resultApi->getForPollResultItem($pollId);
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
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

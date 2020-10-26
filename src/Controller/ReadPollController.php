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
 * Show the poll:
 * - Subject
 * - Proposals
 * - Grades
 * - Results, if the poll allows
 * - Button to participate
 *
 * @Route(
 *     path="/polls/{pollId}",
 *     name="read_poll",
 *     requirements={"pollId"="[^.]+"},
 * )
 * @Route(
 *     path="/polls/{pollId}.html",
 *     name="read_poll_html",
 *     requirements={"pollId"="[^.]+"},
 * )
 */
class ReadPollController extends AbstractController
{
    public function __invoke(
        string $pollId,
        ApiFactory $apiFactory
    )
    {
        $apiInstance = $apiFactory->getPollApi();

        $result = null;
        try {
            $result = $apiInstance->getPollItem($pollId);
//            [$result, $code, $headers] = $apiInstance->getPollItemWithHttpInfo($pollId);
        } catch (ApiException $e) {
            if (Response::HTTP_NOT_FOUND == $e->getCode()) {
                throw new NotFoundHttpException("No poll found.");
            }

//            echo 'Exception when calling Api: ', $e->getMessage(), PHP_EOL;
            throw $e;
        }

        return $this->render('poll/read.html.twig', [
            'poll' => $result,
        ]);
    }
}

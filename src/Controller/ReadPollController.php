<?php

namespace App\Controller;

use MjOpenApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


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
 *     requirements={"pollId"="[^./]+"},
 * )
 * @Route(
 *     path="/polls/{pollId}.html",
 *     name="read_poll_html",
 *     requirements={"pollId"="[^./]+"},
 * )
 */
class ReadPollController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(string $pollId)
    {
        $apiInstance = $this->getApiFactory()->getPollApi();

        $pollRead = null;
        try {
            $pollRead = $apiInstance->getPollItem($pollId);
        } catch (ApiException $e) {
            if (Response::HTTP_NOT_FOUND == $e->getCode()) {
                throw new NotFoundHttpException("No poll found.");
            }
            return $this->renderApiException($e);
        }

        return $this->render('poll/read.html.twig', [
            'poll' => $pollRead,
        ]);
    }
}

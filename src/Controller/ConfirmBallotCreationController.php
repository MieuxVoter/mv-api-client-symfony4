<?php

namespace App\Controller;

use App\Adapter\ApiExceptionAdapter;
use MjOpenApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route(
 *     path="/polls/{pollId}/aftermath.html",
 *     name="confirm_ballot_created_html",
 *     requirements={"pollId"="[a-zA-Z0-9-]+"},
 * )
 */
class ConfirmBallotCreationController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(
        string $pollId,
        Request $request,
        ApiExceptionAdapter $exceptionAdapter
    )
    {
        /// REFACTOR ME ///
        $apiInstance = $this->getApiFactory()->getPollApi();
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

        return $this->render('ballot/created.html.twig', [
            'poll' => $pollRead,
        ]);
    }

}

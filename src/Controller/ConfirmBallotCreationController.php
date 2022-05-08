<?php

namespace App\Controller;

use App\Adapter\ApiExceptionAdapter;
use MvApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/** @noinspection PhpUnused */
/**
 * @Route(
 *     path="/polls/{pollId}/aftermath.html",
 *     name="confirm_ballot_created_html",
 *     requirements={"pollId"="[a-zA-Z0-9-]+"},
 * )
 */
final class ConfirmBallotCreationController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(
        string $pollId,
        Request $request
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
            return $this->renderApiException($e, $request);
//            throw $e;
        }
        ///////////////////

        return $this->render('ballot/created.html.twig', [
            'poll' => $pollRead,
        ]);
    }

}

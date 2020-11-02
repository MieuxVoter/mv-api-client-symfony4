<?php

declare(strict_types=1);

namespace App\Controller;

use MjOpenApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route(
 *     path="/polls/{pollId}/invitations.csv",
 *     name="generate_invitations_csv",
 *     requirements={"pollId"="[^./]+"},
 * )
 */
final class GenerateInvitationsController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(string $pollId, Request $request) : Response
    {
        $invitationsApi = $this->getApiFactory()->getInvitationApi();

        $page = 1;
        try {
            $invitations = $invitationsApi->getForPollInvitationCollection($pollId, $page);
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
        }

        $response = $this->render('poll/invitations.csv.twig', [
            'invitations' => $invitations,
        ]);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="invitations-'.$pollId.'.csv"'
        );

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use MvApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Regexes;

/**
 * @Route(
 *     path="/polls/{pollId}/invitations.csv",
 *     name="generate_invitations_csv",
 *     requirements={
 *       "pollId"=Regexes::UUID,
 *       "page"=Regexes::UINT,
 *     },
 *     defaults={
 *       "page"=1,
 *     },
 * )
 * @Route(
 *     path="/polls/{pollId}/invitations_{page}.csv",
 *     name="generate_invitations_csv_paginated",
 *     requirements={
 *       "pollId"=Regexes::UUID,
 *       "page"=Regexes::UINT,
 *     },
 * )
 */
final class GenerateInvitationsController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(string $pollId, int $page, Request $request): Response
    {
        $invitationsApi = $this->getApiFactory()->getInvitationApi();

        $page = $request->query->getInt('page', $page);

        try {
            // fixme: $page appears ignored -> upstream
            $invitations = $invitationsApi->getForPollInvitationCollection($pollId, $page);
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
        }

        $response = $this->render('poll/invitations.csv.twig', [
//            'invitations' => $invitations,
            // Generated client lib now requires ->getHydramember()
            // I don't like this ; let's try and fix this upstream…
            'invitations' => $invitations->getHydramember(),
        ]);

        // These tell the browser to start a download instead of opening a new tab
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="invitations-' . $pollId .  '-' . $page . '.csv"'
        );

        return $response;
    }
}

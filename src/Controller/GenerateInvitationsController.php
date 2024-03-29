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
        $page = $request->query->getInt('page', $page);
        if (0 >= $page) {
            $page = 1;  // pages start at 1, yoda says
        }

        try {
            // fixme(upstream): $page appears ignored
            $invitations = $this
                ->getApiFactory()->getInvitationApi()
                ->getForPollInvitationCollection($pollId, 100, $page);
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
        }

        $response = $this->render('poll/invitations.csv.twig', [
            'invitations' => $invitations,
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

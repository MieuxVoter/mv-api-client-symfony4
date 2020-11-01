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
 *     path="/invitations/{invitationId}.html",
 *     name="accept_invitation_html",
 *     requirements={"invitationId"="[^./]+"},
 * )
 */
final class AcceptInvitationController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(string $invitationId, Request $request) : Response
    {
        $invitationApi = $this->getApiFactory()->getInvitationApi();

        try {
            $invitation = $invitationApi->getInvitationItem($invitationId);
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
        }

        return $this->render('invitation/accept.html.twig', [
            'invitation' => $invitation,
        ]);
    }
}

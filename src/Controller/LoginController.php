<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\Credentials;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Twig\Environment as TwigEnvironment;


/**
 * Note: we never go through this as POST, since the Guard redirects before that.
 *
 * @Route(
 *     path="/login",
 *     name="login",
 * )
 * @Route(
 *     path="/login.html",
 *     name="login_html",
 * )
 */
final class LoginController extends AbstractController
{
    use Has\ApiAccess;
    use Has\Translator;
    use Has\UserSession;
    use TargetPathTrait;

    public function __invoke(
        Request $request,
        TwigEnvironment $twig,
        SessionInterface $session,
        FormFactoryInterface $formFactory,
        AuthenticationUtils $authenticationUtils
    ): Response {

        $form = $formFactory->createNamed('', LoginType::class, [
            'username' => $authenticationUtils->getLastUsername(),
        ]);
        $form->handleRequest($request);

        // redirection shenanigans //////////////////////////////////////////
        if ($request->getMethod() == Request::METHOD_GET) {
            $redirect = $request->get('redirect');
            if ( ! empty($redirect)) {
                $session->set("login_redirect", $redirect);
                $session->set("register_redirect", $redirect);
                $this->saveTargetPath($session, 'main', $redirect);
            }
        }
        /////////////////////////////////////////////////////////////////////

        return $this->render('user/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ]);
    }
}

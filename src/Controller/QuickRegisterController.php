<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/** @noinspection PhpUnused */


/**
 * Quick registration with random username and password.
 *
 * @Route(
 *     path="/quick-register.html",
 *     name="quick_register",
 * )
 */
final class QuickRegisterController extends AbstractController
{
    const SESSION_ONE_CLICK = 'is_one_click';

    use Has\ApiAccess;
    use Has\Translator;
    use Has\UserSession;

    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        SessionInterface $session,
        GuardAuthenticatorHandler $guard,
        MessageBusInterface $bus
    ): Response
    {
        // The redirection utilities should probably be moved to a trait as well.
        // There's need for them in other controllers.
        $redirect = $request->get('redirect', null);
        if (null !== $redirect) {
            $session->set('register_redirect', $redirect);
        }
        /////////////////////////////////////////////////////////////////////////

        if ($this->getUserSession()->isLogged()) {
            return $this->respondRedirection($session);
        }

        $registered = $this->quickRegister($request, $guard);
        if (true !== $registered) {
            return $registered;
        }

        $this->getUserSession()->setUserProperty(self::SESSION_ONE_CLICK, true);
//        $this->getUser()->

        $flashBag->add('success', 'flash.user.registered');

        return $this->respondRedirection($session);
    }

    protected function respondRedirection(SessionInterface $session): Response
    {
        // We could use our (new) Crumbs service here, but this works.
        $redirect = $session->get('register_redirect', $this->generateUrl('home_html'));
        $session->remove("login_redirect");
        $session->remove("register_redirect");

        return new RedirectResponse($redirect);
    }

}

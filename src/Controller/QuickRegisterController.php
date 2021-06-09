<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Security\UserSession;
use MjOpenApi\ApiException;
use MjOpenApi\Model\Credentials;
use MjOpenApi\Model\UserCreate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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
    use Has\ApiAccess;
    use Has\Translator;
    use Has\UserSession;

    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        SessionInterface $session,
        UserSession $userSession,
        GuardAuthenticatorHandler $guard,
        MessageBusInterface $bus
    ): Response {
        $redirect = $request->get('redirect', null);
        if (null !== $redirect) {
            $session->set('register_redirect', $redirect);
        }

        if ($userSession->isLogged()) {
            return $this->respondRedirection($session);
        }

        $userApi = $this->getApiFactory()->getUserApi();

        $passwordPlain = uniqid();

        $userCreate = new UserCreate();
        $userCreate->setPassword($passwordPlain);

        $userRead = null;
        try {
            $userRead = $userApi->postUserCollection($userCreate);
        } catch (ApiException $e) {
            return new Response("Quick registration failed: " . $e->getMessage());
        }

        // The registration seemed to work.
        // Let's login, if we can, after a fashion

        sleep(1);

        $username = $userRead->getUsername();

        $tokenApi = $this->getApiFactory()->getTokenApi();

        $credentials = new Credentials();
        $credentials->setUsernameOrEmail($username);
        $credentials->setPassword($passwordPlain);

        $token = null;
        try {
            $token = $tokenApi->postCredentialsItem($credentials);
        } catch (ApiException $e) {
            // Registration was a success, but login was not.
            return $this->renderApiException($e, $request);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setApiToken($token->getToken());

        $this->userSession->login(
            $userRead->getUuid(),
            $username,
            $token->getToken()
        );
        $this->getApiFactory()->setApiToken($token->getToken());

        // Authenticate with Symfony
        $t = new UsernamePasswordToken($user, null, 'mvapi_users', $user->getRoles());
        $guard->authenticateWithToken($t, $request, 'mvapi_users');
        
        // Wipe the memory…
        $password = uniqid();
        $passwordPlain = uniqid();
        $token = null;

        $flashBag->add('success', 'flash.user.registered');

        return $this->respondRedirection($session);
    }

    protected function respondRedirection(SessionInterface $session) : Response
    {
        $redirect = $session->get('register_redirect', $this->generateUrl('home_html'));
        $session->remove("login_redirect");
        $session->remove("register_redirect");

        return new RedirectResponse($redirect);
    }

}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Security\ApiGuardAuthenticator;
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
 * @Route(
 *     path="/register.html",
 *     name="register",
 * )
 */
final class RegisterController extends AbstractController
{
    use Has\ApiAccess;
    use Has\Translator;
    use Has\UserSession;

    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        SessionInterface $session,
        MessageBusInterface $bus,
        GuardAuthenticatorHandler $guard,
        ApiGuardAuthenticator $authenticator
    ): Response {
        $redirect = $request->get('redirect', null);
        if (null !== $redirect) {
            $session->set('register_redirect', $redirect);
        }
        $form = $formFactory->createNamed('', RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Let's skip this for now
//            $bus->dispatch(new CreateUser($form->getData()));

            $userApi = $this->getApiFactory()->getUserApi();
            $data = $form->getData();
            $username = $data['username'];
            $email = $data['email'];
            $password = $request->get('password');
            $passwordConfirm = $request->get('password_confirm');
            if ($password !== $passwordConfirm) {
                $form->addError(new FormError($this->trans('form.register.error.passwords_mismatch')));
                return $this->renderForm($form);
            }
            $passwordPlain = $password;

            $userCreate = new UserCreate();
            $userCreate->setEmail($email);
            $userCreate->setUsername($username);
            $userCreate->setPassword($passwordPlain);

            $userRead = null;
            try {
                $userRead = $userApi->postUserCollection($userCreate);
            } catch (ApiException $e) {
                $this->getApiExceptionAdapter()->setFormErrorsIfAny($form, $e);

                return $this->getApiExceptionAdapter()->respond(
                    $e,
                    $this->renderForm($form)
                );
            }

            // The registration seemed to work.
            // Let's login, if we can, after a fashion

            sleep(1);

            $tokenApi = $this->getApiFactory()->getTokenApi();

            $credentials = new Credentials();
            $credentials->setUsernameOrEmail($username);
            $credentials->setPassword($passwordPlain);

            $token = null;
            try {
                $token = $tokenApi->postCredentialsItem($credentials);
            } catch (ApiException $e) {
                // Registration was a success, but login was not.
                // Poll Subject: What should we do here?
                return $this->renderApiException($e, $request);                            // Proposal A
//                return new RedirectResponse($redirect);                        // Proposal B
//                return new RedirectResponse($this->generateUrl('login_html')); // Proposal C
            }

            // All's well!  Save the JWT in the session.
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
            // libsodium installation hassle
            // → not worth it?  (perhaps later?)
//            memzero($password);
//            memzero($passwordPlain);
            $username = uniqid();
            $password = uniqid();
            $passwordPlain = uniqid();
            $token = uniqid();

            $flashBag->add('success', 'flash.user.registered');

            $redirect = $session->get('register_redirect', $this->generateUrl('home_html'));
            $session->remove("login_redirect");
            $session->remove("register_redirect");

//            return $guard->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'mvapi_users');
            return new RedirectResponse($redirect);
        }

        return $this->renderForm($form);
    }

    protected function renderForm(FormInterface $form)
    {
        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

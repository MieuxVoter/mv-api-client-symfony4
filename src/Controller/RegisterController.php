<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RegisterType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\Credentials;
use MjOpenApi\Model\UserCreate;
use MsgPhp\User\Command\CreateUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
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
        MessageBusInterface $bus
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
            //$password = $data['password']; // encrypted
            $password = $request->get('password', ['plain' => null]);
            if ( ! isset($password['plain'])) {
                $form->addError(new FormError($this->trans('form.register.error.password_shape')));
                return $this->renderForm($form);
            }
            $passwordPlain = $password['plain'];

            $userCreate = new UserCreate();
            $userCreate->setEmail($email);
            $userCreate->setUsername($username);
            $userCreate->setPassword($passwordPlain);

            $userRead = null;
            try {
                $userRead = $userApi->postUserCollection($userCreate);
            } catch (ApiException $e) {
//                return $this->renderApiException($e);
                $this->getApiExceptionAdapter()->setFormErrorsIfAny($form, $e);

                return $this->getApiExceptionAdapter()->respond(
                    $e,
                    $this->renderForm($form)
                );
            }

            // The registration seemed to work.
            // Let's login, if we can, after a fashion

            sleep(0.5);

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
                return $this->renderApiException($e);                            // Proposal A
//                return new RedirectResponse($redirect);                        // Proposal B
//                return new RedirectResponse($this->generateUrl('login_html')); // Proposal C
            }

            ///
            ///

            // All's well!  Save the JWT in the session.

            $this->userSession->login(
                $userRead->getUuid(),
                $username,
                $token->getToken()
            );
            $this->getApiFactory()->setToken($token->getToken());

            // Wipe the memory…
            // libsodium installation hassle
            // → not worth it?  (perhaps later?)
//            memzero($password['plain']);
//            memzero($passwordPlain);
            $password['plain'] = "Elleavaitprisceplidanssonâgeenfantin";
            $passwordPlain = "Devenirdansmachambreunpeuchaquematin";
            $token = null;

            $flashBag->add('success', 'flash.user.registered');

            $redirect = $session->get('register_redirect', $this->generateUrl('home_html'));
            return new RedirectResponse($redirect);
//            return new RedirectResponse($this->generateUrl('login', ['redirect' => $redirect]));
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

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
 * This is disconnected from MsgPHP login.
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


        if ($request->getMethod() == Request::METHOD_GET) {
            // redirect shenanigans ///////////////////
            $redirect = $request->get('redirect');
            if ( ! empty($redirect)) {
                $session->set("login_redirect", $redirect);
                //$this->saveTargetPath($session, 'main', $redirect);
            }
            ///////////////////////////////////////////
//        } else if ($request->getMethod() == Request::METHOD_POST) {
        } else if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $credentials = new Credentials();
            $credentials->setUsernameOrEmail($data['username']);
            $credentials->setPassword($data['password']);

            $token = null;
            try {
                $token = $this->getApiFactory()->getTokenApi()->postCredentialsItem($credentials);
            } catch (ApiException $e) {
                $apiResponseData = $this->getApiExceptionData($e);
                if (
                    ($apiResponseData)
                    &&
                    (isset($apiResponseData['code']))
                    &&
                    (Response::HTTP_UNAUTHORIZED == $apiResponseData['code'])
                ) {

                    $form->addError(new FormError(
                        $this->trans('form.login.error.unauthorized')
                    ));
                    return $this->render('user/login.html.twig', [
                        'error' => null,
//                        'error' => [
//                            'messageKey' => "form.login.error.unauthorized",
//                            'messageData' => [],
//                        ],
                        'form' => $form->createView(),
                    ]);
                }

                return $this->renderApiException($e);
            }

            if (null === $token) {
                trigger_error("core.error.token.empty");
            } else {
                $this->userSession->login('', $data['username'], $token->getToken());
                $redirect = $session->get("login_redirect", $this->generateUrl('home_html'));
                $session->remove("login_redirect");
                return new RedirectResponse($redirect);
            }

        }

        return $this->render('user/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ]);
    }
}

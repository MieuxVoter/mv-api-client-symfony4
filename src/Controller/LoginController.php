<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\Credentials;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Twig\Environment as TwigEnvironment;


/**
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
    use TargetPathTrait;

    public function __invoke(
        Request $request,
        TwigEnvironment $twig,
        SessionInterface $session,
        FormFactoryInterface $formFactory,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $form = $formFactory->createNamed('', LoginType::class, [
            'email' => $authenticationUtils->getLastUsername(),
        ]);

        //
        $form->handleRequest($request);


        if ($request->getMethod() == Request::METHOD_GET) {
            // redirect shenanigans we'll probably drop
            $redirect = $request->get('redirect');
            if (!empty($redirect)) {
                $this->saveTargetPath($session, 'main', $redirect);
            }
            ///////////////////////////////////////////
        } else if ($request->getMethod() == Request::METHOD_POST) {

//            dd($form);
//            dd($form->getData());

            $data = $form->getData();

            $credentials = new Credentials();
            $credentials->setUsernameOrEmail($data['email']);
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
            } else {
                // fixme: ok!
                dd($token);
//                $session->set(User::SESSION_KEY_USERNAME, $data['email']);
//                dd($token);
            }

        }

        return $this->render('user/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
 *     path="/login.html",
 *     name="login",
 * )
 */
final class LoginController extends AbstractController
{
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

        if ($request->getMethod() == Request::METHOD_GET) {
            $redirect = $request->get('redirect');
            if (!empty($redirect)) {
                $this->saveTargetPath($session, 'main', $redirect);
            }
        }

        return new Response($twig->render('user/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ]));
    }
}

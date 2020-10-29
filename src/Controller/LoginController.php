<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

/**
 * @Route("/login", name="login")
 */
final class LoginController
{
    public function __invoke(
        Environment $twig,
        FormFactoryInterface $formFactory,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $form = $formFactory->createNamed('', LoginType::class, [
            'email' => $authenticationUtils->getLastUsername(),
        ]);

        return new Response($twig->render('user/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ]));
    }
}

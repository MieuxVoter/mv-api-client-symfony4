<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ClaimUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;


/** @noinspection PhpUnused */
/**
 * @Route("/claim", name="app_claim_user")
 * @Route("/claim.html", name="app_claim_user_html")
 */
class ClaimUserController extends AbstractController
{
    use Has\ApiAccess;
    use Has\UserSession;

    public function __invoke(
        Request $request,
        RouterInterface $router
    ): Response
    {
        $user = $this->getUser();
        if (empty($user)) {
            return RedirectResponse::create($router->generate("home_html"));
        }
        $username = $user->getUsername();

        /** @var Form $password_form */
//        $password_form = $this->createForm(ChangePasswordType::class, $ballot, $options);
        $password_form = $this->createForm(
            ClaimUserType::class, [
                'username' => $username,
            ], [
//                'attr' => [
//                    'class' => 'form-horizontal',
//                ],
            ]
        );
        $password_form->handleRequest($request);

        $shouldSend = $password_form->isSubmitted() && $password_form->isValid();

        // FIXME: actually implement the request to OAS

        return $this->render('user/claim.html.twig', [
//            'controller_name' => 'ClaimUserController',
            'password_form' => $password_form->createView(),
        ]);
    }
}

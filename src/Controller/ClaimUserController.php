<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ClaimUserType;
use MjOpenApi\ApiException;
use MjOpenApi\Model\UserEdit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;


/** @noinspection PhpUnused */
/**
 * @Route("/claim", name="claim_user")
 * @Route("/claim.html", name="claim_user_html")
 */
class ClaimUserController extends AbstractController
{
    use Has\ApiAccess;
    use Has\UserSession;
    use Has\RedirectionCrumbs;

    public function __invoke(
        Request $request,
        RouterInterface $router
    ): Response
    {
        $session_user = $this->getUserSession()->getUser();
        $user = $this->getUser();
        if (empty($user)) {
            return RedirectResponse::create($router->generate("home_html"));
        }
        $username = $user->getUsername();

        $claim_form = $this->createForm(
            ClaimUserType::class, [
                'username' => $username,
            ], [
                'attr' => [
//                    'class' => 'form-horizontal',
                ],
            ]
        );
        $claim_form->handleRequest($request);

        $should_send = $claim_form->isSubmitted() && $claim_form->isValid();

        dump($claim_form->getData());
        dump($session_user);

        if ($should_send) {
            $user_api = $this->getApiFactory()->getUserApi();

            $form_data = $claim_form->getData();
            $password = $form_data['password'] ?? null;
            $email = $form_data['email'] ?? null;
            $user_edit = new UserEdit();
            if ( ! empty($password)) {
                $user_edit->setPassword($password);
            }
            if ( ! empty($email)) {
                $user_edit->setEmail($email);
            }

            try {
                $user_response = $user_api->putUserItem($session_user['id'], $user_edit);
            } catch (ApiException $e) {
                return $this->renderApiException($e, $request);
//                throw $e;
            }

            dump($user_response);

            $password = uniqid(); // fixme: use a proper memzero
            unset($password);
            unset($form_data);

            // Redirect to where we were before we started claiming our account
            return $this->redirectToCrumb();
        }

        return $this->render('user/claim.html.twig', [
//            'controller_name' => 'ClaimUserController',
            'claim_form' => $claim_form->createView(),
        ]);
    }
}

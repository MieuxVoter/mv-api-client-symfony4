<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RegisterType;
use MsgPhp\User\Command\CreateUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;


/**
 * @Route(
 *     path="/register.html",
 *     name="register",
 * )
 */
final class RegisterController extends AbstractController
{
    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        Environment $twig,
        MessageBusInterface $bus
    ): Response {
        $redirect = $request->get('redirect', null);
        $form = $formFactory->createNamed('', RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bus->dispatch(new CreateUser($form->getData()));
            $flashBag->add('success', 'flash.user.registered');
            return new RedirectResponse($this->generateUrl('login', ['redirect' => $redirect]));
        }

        return new Response($twig->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}

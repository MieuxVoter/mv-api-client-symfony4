<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/** @noinspection PhpUnused */

/**
 * @Route("/", name="home")
 * @Route("/home.html", name="home_html")
 */
class HomeController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(): Response
    {
        // TODO: check API status, perhaps?

        return $this->render('home/home.html.twig', []);
    }
}

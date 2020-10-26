<?php

namespace App\Controller;

use App\Factory\ApiFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @Route("/home.html", name="home_html")
     */
    public function index(
        ApiFactory $apiFactory
    ): Response
    {
        // TODO: check API status, perhaps?

        return $this->render('home/home.html.twig', []);
    }
}

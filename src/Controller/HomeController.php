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
    use Has\Translator;

    public function __invoke(): Response
    {
        // TODO: check API status, perhaps?   With some caching?
//        $online = $this->getApiStatus()->isOnline();
//        $someStat = $this->getApiStatus()->getSomeStat();

        return $this->render('home/home.html.twig', [
            // perhaps transArray should be added as a Twig filter so we can remove this
            'suggestions' => $this->transArray('page.home.suggestions'),
        ]);
    }
}

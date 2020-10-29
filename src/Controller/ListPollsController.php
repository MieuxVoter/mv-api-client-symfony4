<?php

namespace App\Controller;

use App\Factory\ApiFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Show a list of public polls.
 *
 * @Route("/polls", name="list_polls")
 * @Route("/polls.html", name="list_polls_html")
 */
class ListPollsController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(): Response
    {
        $pollApi = $this->getApiFactory()->getPollApi();
        $polls = $pollApi->getPollCollection();

        return $this->render('poll/index.html.twig', [
            'polls' => $polls,
        ]);
    }
}

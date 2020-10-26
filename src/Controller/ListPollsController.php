<?php

namespace App\Controller;

use App\Factory\ApiFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListPollsController extends AbstractController
{
    /**
     * @Route("/polls", name="list_polls")
     * @Route("/polls.html", name="list_polls_html")
     */
    public function index(
        ApiFactory $apiFactory
    ): Response
    {
        $pollApi = $apiFactory->getPollApi();
        $polls = $pollApi->getPollCollection();

        return $this->render('poll/index.html.twig', [
            'polls' => $polls,
        ]);
    }
}

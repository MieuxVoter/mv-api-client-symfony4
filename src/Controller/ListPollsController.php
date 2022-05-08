<?php

namespace App\Controller;

use MvApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    public function __invoke(Request $request): Response
    {
        $pollApi = $this->getApiFactory()->getPollApi();

        try {
            $polls = $pollApi->getPollCollection();
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
        }

        return $this->render('poll/index.html.twig', [
            'polls' => $polls,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Service\GradePalettePainter;
use Miprem\Model\Poll;
use Miprem\Renderer\OpenGraphRenderer;
use Miprem\Renderer\SvgRenderer;
use Miprem\Model\SvgConfig;
use MjOpenApi\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * For now the result id is the poll id.
 * But results may be 'hidden' and have their own id.
 *
 * @Route(
 *     path="/results/{resultId}.html",
 *     name="read_result_html",
 *     requirements={"id"="[^.]+"},
 * )
 */
final class ReadResultController extends AbstractController
{
    use Has\ApiAccess;

    public function __invoke(
        string $resultId,
        Request $request,
        GradePalettePainter $palette
    ): Response
    {
        $pollId = $resultId; // TBD: Result id? (could be kept secret?)
        $pollApi = $this->getApiFactory()->getPollApi();
        $resultApi = $this->getApiFactory()->getResultApi();

        $poll = null;
        $result = null;
        try {
            $poll = $pollApi->getPollItem($pollId);
            $result = $resultApi->getForPollResultItem($pollId);
        } catch (ApiException $e) {
            return $this->renderApiException($e, $request);
        }

        $grades = [];
        foreach ($poll->getGrades() as $grade) {
            $grades['/grades/' . $grade->getUuid()] = $grade;
        }

        /** @var array<array<int>> $tally */
        $tally = [];
        foreach ($result->getLeaderboard() as $proposalResultRead) {
            $proposalTally = [];
            foreach ($proposalResultRead->getGradesResults() as $gradeResult) {
                $proposalTally[] = $gradeResult->getTally();
            }
            $tally[] = array_reverse($proposalTally);
        }

        $mipremPoll = Poll::fromArray([
//            'default_grades' => [
//                ['label' => 'Reject'],
//                ['label' => 'Insufficient'],
//                ['label' => 'Poor'],
//                ['label' => 'Fair'],
//                ['label' => 'Good'],
//                ['label' => 'Very good'],
//                ['label' => 'Excellent'],
//            ],
            'tally' => $tally,
            'subject' => [
                'label' => $poll->getSubject(),
            ],
        ]);

        $svg_config = SvgConfig::sample()
            ->setHeaderHeight(0)
            ->setPadding(0)
            ->setSidebarWidth(0);
//        $svg_style = new Style(<<<SVG_CSS
///*
//svg {
//  border: 3px dashed chartreuse;
//}
//*/
//SVG_CSS
//        );
        $svgRenderer = new SvgRenderer($svg_config);
        $meritProfileSvg = $svgRenderer->render($mipremPoll);

        $ogRenderer = new OpenGraphRenderer(1200, 630);
        $pollOpenGraph = $ogRenderer->render($mipremPoll);

        return $this->render('poll/result.html.twig', [
            'poll' => $poll,
            'result' => $result,
            'grades' => $grades,
            'palette' => $palette->makePalette(count($grades)),
            'meritProfileSvg' => $meritProfileSvg,
            'pollOpenGraph' => $pollOpenGraph,
        ]);
    }
}

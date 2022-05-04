<?php

namespace App\Controller;

use App\Service\GradePalettePainter;
use Miprem\Model\Poll as RenderedPoll;
use Miprem\Renderer\OpenGraphRenderer;
use Miprem\Renderer\SvgRenderer;
use Miprem\Model\SvgConfig;
use MjOpenApi\ApiException;
use MjOpenApi\Model\GradeRead;
use MjOpenApi\Model\ProposalResultRead;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/** @noinspection PhpUnused */
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
    use Has\Gradings;

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

        /** @var array<array<int>> $pollTally */
        $pollTally = [];
        foreach ($result->getLeaderboard() as $proposalResultRead) {
            $proposalTally = [];
            foreach ($proposalResultRead->getGradesResults() as $gradeResult) {
                $proposalTally[] = $gradeResult->getTally();
            }
            // Wait… what?  Why do we need to reverse here?  Is it Miprem's API?
            //$pollTally[] = $proposalTally;
            $pollTally[] = array_reverse($proposalTally);
        }

        $renderedPoll = RenderedPoll::fromArray([
            'grades' => array_reverse(array_map(function (GradeRead $grade) {
                return ['label' => $grade->getName()];
            }, $poll->getGrades())),
            'proposals' => array_map(function (ProposalResultRead $proposal) {
                return ['label' => $proposal->getProposal()->getTitle()];
            }, $result->getLeaderboard()),
            'tally' => $pollTally,
            'subject' => [
                'label' => $poll->getSubject(),
            ],
        ]);

        $svgConfig = SvgConfig::sample()
            ->setHeaderHeight(0)
            ->setPadding(10)
            ->setSidebarWidth(0);
//        $svg_style = new Style(<<<SVG_CSS
///*
//svg {
//  border: 3px dashed chartreuse;
//}
//*/
//SVG_CSS
//        );
        $svgRenderer = new SvgRenderer($svgConfig);
        $meritProfileSvg = $svgRenderer->render($renderedPoll);

        $ogRenderer = new OpenGraphRenderer(1200, 630);
        $pollOpenGraph = $ogRenderer->render($renderedPoll);

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

<?php


namespace App\Service;


use Miprem\Renderer\SvgRenderer;
use Miprem\Style;
use Miprem\SvgConfig;

class GradePalettePainter
{

    const COLOR_0 = "#DF3222";
    const COLOR_1 = "#ED6F01";
    const COLOR_2 = "#FAB001";
    const COLOR_3 = "#C5D300";
    const COLOR_4 = "#7BBD3E";
    const COLOR_5 = "#00A249";
    const COLOR_6 = "#017A36";

    protected $palettes = [
        0 => [],
        1 => [self::COLOR_0],
        2 => [
            self::COLOR_0,
            self::COLOR_6,
        ],
        3 => [
            self::COLOR_0,
            self::COLOR_2,
            self::COLOR_6,
        ],
        4 => [
            self::COLOR_0,
            self::COLOR_2,
            self::COLOR_4,
            self::COLOR_6,
        ],
        5 => [
            self::COLOR_0,
            self::COLOR_2,
            self::COLOR_4,
            self::COLOR_5,
            self::COLOR_6,
        ],
        6 => [
            self::COLOR_0,
            self::COLOR_1,
            self::COLOR_2,
            self::COLOR_4,
            self::COLOR_5,
            self::COLOR_6,
        ],
        7 => [
            self::COLOR_0,
            self::COLOR_1,
            self::COLOR_2,
            self::COLOR_3,
            self::COLOR_4,
            self::COLOR_5,
            self::COLOR_6,
        ],
    ];

    /**
     * @param int $length The amount of colors in the palette.
     * @return array<string> A bunch of colors in hexadecimal form for CSS
     */
    public function makePalette(int $length) : array
    {
        assert(0 <= $length, "A negative length is absurd.");
        assert($length <= 7, "More than 7 colors is not supported yet");

//        $renderer = new SvgRenderer(new SvgConfig(), new Style());
//        return $renderer->gradeColor($length);

        return $this->palettes[$length];
    }

}
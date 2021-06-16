<?php

declare(strict_types=1);

namespace App\Controller\Has;

use App\Service\GradePalettePainter;


trait ColorPalette
{

    /** @var GradePalettePainter */
    protected $colorPalettePainter;

    /**
     * @param int $length Amount of colors in the palette
     * @return array<string>
     */
    public function getColorPalette(int $length): array
    {
        return $this->getColorPalettePainter()->makePalette($length);
    }

    /**
     * @return GradePalettePainter
     */
    public function getColorPalettePainter(): GradePalettePainter
    {
        return $this->colorPalettePainter;
    }

    /** @noinspection PhpUnused */
    /**
     * @required
     * @param GradePalettePainter $painter
     */
    public function setColorPalettePainter(GradePalettePainter $painter): void
    {
        $this->colorPalettePainter = $painter;
    }

}
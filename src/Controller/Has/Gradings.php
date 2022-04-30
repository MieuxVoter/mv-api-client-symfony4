<?php

declare(strict_types=1);

namespace App\Controller\Has;

use App\Entity\Grading;
use App\Factory\GradingsFactory;


/**
 * A trait for Controllers to handle grading presets and such.
 *
 * Trait Gradings
 * @package App\Controller\Has
 */
trait Gradings
{
    /**
     * @var GradingsFactory
     */
    protected $gradings_factory;

    /**
     * @return GradingsFactory
     */
    public function getGradingsFactory(): GradingsFactory
    {
        return $this->gradings_factory;
    }

    /**
     * @required (voodoo autoload from DIC)
     * @param GradingsFactory $gradings_factory
     */
    public function setGradingsFactory(GradingsFactory $gradings_factory): void
    {
        $this->gradings_factory = $gradings_factory;
    }

    /**
     * Sugar.  See GradingsFactory.
     * @param string $presetName
     * @return Grading
     */
    public function findGradingFromPresetName(string $presetName): Grading
    {
        return $this->getGradingsFactory()->findGradingFromPresetName($presetName);
    }
}
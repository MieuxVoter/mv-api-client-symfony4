<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Grading;
use App\Has\Translator;


/**
 * Light wrapper around how we store/make the gradings presets.
 * Right now we build them in code, but later on we might have a more sophisticated database.
 *
 * Class GradingsFactory
 * @package App\Service
 */
class GradingsFactory
{
    use Translator;


    public function findGradingFromPresetName(string $presetName): Grading
    {
        // Here we could go look into a (file-based?) database of grading presets
        // For now we'll just hardcode them here ; sorry about that.
        return $this->makeGradingFromPresetName($presetName);
    }


    public function makeGradingFromPresetName(string $presetName): Grading
    {
        $sensible_default_amount = 6; // what's a sensible default to return here ?
        $matches = array();
        $amount_matched = preg_match(
            "!(?<amount>[0-9]+)$!", // later on match color palette in here as well?
            $presetName,
            $matches
        );
        if ($amount_matched === false || $amount_matched === 0) {
            $amountOfGrades = $sensible_default_amount;
        } else {
            $amountOfGrades = $matches['amount'];
        }

        $gradesNames = [];
        for ($i = 0; $i < $amountOfGrades; $i++) {
            $gradesNames[] = $this->trans(
                "${presetName}.grades.${i}", [], 'grades'
            );
        }

        $grading = new Grading($gradesNames);

        return $grading;
    }

}
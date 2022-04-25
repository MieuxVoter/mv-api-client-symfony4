<?php


namespace App\Entity;


/**
 * A set of ordered grades.   The order MUST be obvious for JM to function properly.
 * Grades are stored from "worst" to "best", in arrays.
 *
 * Class Grading
 * @package App\Entity
 */
class Grading
{
    /**
     * @var string[]
     */
    protected $names = array();

    //protected $colors = array();

    /**
     * Grading constructor.
     * @param array $names
     */
    public function __construct(array $names)
//    public function __construct(array $names, array $colors = array())
    {
        $this->names = $names;

        // Set default colors if empty
        //$this->colors = $colors;
    }

    public function getAmountOfGrades(): int
    {
        return count($this->names);
    }

    /**
     * These names ought to already be translated (in the `grades` domain).
     * Not sure this Entity should hold the translated names or not.  It does for now.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

}
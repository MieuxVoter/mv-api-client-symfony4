<?php


namespace App\Entity;


class Ballot
{

    /**
     * For each proposal, the grade that was given.
     * Associative array of proposalId => gradeId
     *
     * @var string[]
     */
    protected $judgments = [];

    ///
    ///

    /**
     * @return string[]
     */
    public function getJudgments(): array
    {
        return $this->judgments;
    }

    /**
     * @param string[] $judgments
     */
    public function setJudgments(array $judgments): void
    {
        $this->judgments = $judgments;
    }

}
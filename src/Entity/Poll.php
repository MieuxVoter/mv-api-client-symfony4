<?php


namespace App\Entity;


class Poll
{
    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string[]
     */
    public $grades = [];

    /**
     * @var string[]
     */
    public $proposals = [];

    ///
    ///

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Poll
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     * @return Poll
     */
    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getProposals(): array
    {
        return $this->proposals;
    }

    /**
     * @param string[] $proposals
     * @return Poll
     */
    public function setProposals(array $proposals): self
    {
        $this->proposals = $proposals;

        return $this;
    }

    /**
     * @param string $title
     * @return Poll
     */
    public function addProposal(string $title) : self
    {
        if (in_array($title, $this->getProposals())) {
            return $this; // skip duplicates
        }

        $this->proposals[] = $title;

        return $this;
    }

}
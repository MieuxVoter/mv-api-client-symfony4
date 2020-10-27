<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class Poll
{
    /**
     * The order matters.
     * These must have definitions in the translations' `grades` domain.
     */
    const GRADING_PRESETS = [
        'quality_2',
        'quality_3',
        'quality_6',
    ];

    const SCOPES = [
        'public',
        'unlisted',
        'private',
    ];

    /**
     * @Assert\Length(
     *     max=142,
     * )
     * @var string
     */
    protected $subject = '';

    /**
     * @Assert\Choice(
     *     choices=self::SCOPES,
     * )
     * @var string
     */
    protected $scope;

    /**
     * @Assert\Choice(
     *     choices=self::GRADING_PRESETS,
     * )
     * @var string
     */
    protected $grading_preset = 'quality_6';

    /**
     * @var string[]
     */
    public $grades = [];

    /**
     * @var string[]
     */
    protected $proposals = [];

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

    /**
     * @return string
     */
    public function getGradingPreset(): string
    {
        return $this->grading_preset;
    }

    /**
     * @param string $grading_preset
     */
    public function setGradingPreset(string $grading_preset): void
    {
        $this->grading_preset = $grading_preset;
    }

}
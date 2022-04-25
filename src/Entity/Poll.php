<?php

namespace App\Entity;

use App\Form\PollType;
use Symfony\Component\Validator\Constraints as Assert;
use function in_array;


class Poll
{
    /**
     * The order matters ; they will be displayed in that order.
     * These must have definitions in the translations' `grades` domain.
     * Only latin letters, numbers and underscores.  NO DOTS.
     * The amount suffix is important aw well, we use it to know how many grades there are.
     */
    const GRADING_PRESETS = [
        'quality_2',
        'quality_3',
        'quality_6',
    ];

    const SCOPE_PUBLIC = 'public';
    const SCOPE_UNLISTED = 'unlisted';
    const SCOPE_PRIVATE = 'private';

    const SCOPES = [
        self::SCOPE_PUBLIC,
        self::SCOPE_UNLISTED,
        self::SCOPE_PRIVATE,
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

    /**
     * Hidden form input, to help processing a dynamic amount of proposals.
     * @var int
     */
    protected $amount_of_proposals = PollType::DEFAULT_AMOUNT_OF_PROPOSALS;

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

    /**
     * @return int
     */
    public function getAmountOfProposals(): int
    {
        return $this->amount_of_proposals;
    }

    /**
     * @param int $amount_of_proposals
     */
    public function setAmountOfProposals(int $amount_of_proposals): void
    {
        $this->amount_of_proposals = $amount_of_proposals;
    }

}
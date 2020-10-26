<?php


namespace App\Entity;


class Poll
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Poll
     */
    public function setSubject(string $subject): Poll
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
    public function setScope(string $scope): Poll
    {
        $this->scope = $scope;

        return $this;
    }

}
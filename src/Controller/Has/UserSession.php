<?php


namespace App\Controller\Has;


use Symfony\Contracts\Translation\TranslatorInterface;


trait UserSession
{

    /** @var \App\Security\UserSession */
    protected $userSession;

    /**
     * @return \App\Security\UserSession
     */
    public function getUserSession(): \App\Security\UserSession
    {
        return $this->userSession;
    }

    /**
     * @required
     * @param \App\Security\UserSession $userSession
     */
    public function setUserSession(\App\Security\UserSession $userSession): void
    {
        $this->userSession = $userSession;
    }

}
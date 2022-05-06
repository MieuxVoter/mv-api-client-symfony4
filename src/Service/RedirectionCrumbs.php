<?php

declare(strict_types=1);

namespace App\Service;


use App\Security\UserSession;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RedirectionCrumbs
{
    /**
     * @var UserSession
     */
    protected $userSession;

    /**
     * @var SessionInterface $session
     */
    protected $session;

    /**
     * @return UserSession
     */
    public function getUserSession(): UserSession
    {
        return $this->userSession;
    }

    /**
     * @required
     * @param UserSession $userSession
     */
    public function setUserSession(UserSession $userSession): void
    {
        $this->userSession = $userSession;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * @required
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    public function addCrumb(string $path): void
    {
        // cheap but effective for now, we don't yet need many crumbs
        $this->getSession()->set("redirection_crumb", $path);
    }

    public function readCrumb(): ?string
    {
        return $this->getSession()->get("redirection_crumb", '');
    }

    public function forgetCrumb(): void
    {
        $this->getSession()->remove("redirection_crumb");
    }

    public function consumeCrumb(): ?string
    {
        $crumb = $this->readCrumb();
        $this->forgetCrumb();
        return $crumb;
    }
}

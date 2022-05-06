<?php

declare(strict_types=1);

namespace App\Security;


use Symfony\Component\HttpFoundation\Session\SessionInterface;


final class UserSession
{
    const SESSION_USER = 'mv_user';

    /** @var SessionInterface */
    protected $session;

    /**
     * UserSession constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $key
     * @param mixed $value Any primitive that can go into a PHP session variable.
     */
    public function setUserProperty($key, $value): void
    {
        $user = $this->getUser();
        if (empty($user)) {
            return;  // or throw
        }

        $user[$key] = $value;

        $this->session->set(self::SESSION_USER, $user);
    }

    public function login(string $id, string $username, string $token)
    {
        $this->session->set(self::SESSION_USER, [
            'id' => $id,
            'username' => $username,
            'token' => $token,
        ]);
    }

    public function logout()
    {
        $this->session->set(self::SESSION_USER, null);
    }

    public function getUser() : ?array  # defined in login() above
    {
        return $this->session->get(self::SESSION_USER);
    }

    public function isLogged()
    {
        $user = $this->session->get(self::SESSION_USER);
        return ( ! empty($user));
    }
}
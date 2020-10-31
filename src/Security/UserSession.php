<?php


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

    public function getUser() : ?array
    {
        return $this->session->get(self::SESSION_USER);
    }

    public function isLogged()
    {
        $user = $this->session->get(self::SESSION_USER);
        return ( ! empty($user));
    }
}
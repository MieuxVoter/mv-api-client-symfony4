<?php

namespace App\Twig;

use App\Security\UserSession;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UserSessionExtension extends AbstractExtension
{
    /** @var UserSession */
    protected $userSession;

    /**
     * UserSessionExtension constructor.
     * @param UserSession $userSession
     */
    public function __construct(UserSession $userSession)
    {
        $this->userSession = $userSession;
    }

//    /**
//     * @required
//     * @return UserSession
//     */
//    public function getUserSession(): UserSession
//    {
//        return $this->userSession;
//    }



//    public function getFilters(): array
//    {
//        return [
//            // If your filter generates SAFE HTML, you should add a third
//            // parameter: ['is_safe' => ['html']]
//            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
//            new TwigFilter('filter_name', [$this, 'doSomething']),
//        ];
//    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user_logged', [$this, 'isUserLogged']),
            new TwigFunction('get_user', [$this, 'getUser']),
        ];
    }

    public function isUserLogged()
    {
        return $this->userSession->isLogged();
    }

    /**
     * Not a User entity, just an array with what's in the session:
     * - id
     * - username
     *
     * @return array|null
     */
    public function getUser()
    {
        return $this->userSession->getUser();
    }

}

<?php

declare(strict_types=1);

namespace App\Security;


use App\Entity\User;
use App\Factory\ApiFactory;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


/**
 * We don't actively use this for now, but we will.
 * We do use this to inject data from our custom user session into the User.
 *
 * Class UserProvider
 * @package App\Security
 */
final class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    /** @var ApiFactory */
    protected $apiFactory;

    /**
     * @var UserSession
     */
    private $userSession;

    /**
     * UserProvider constructor.
     * @param ApiFactory $apiFactory
     * @param UserSession $userSession
     */
    public function __construct(ApiFactory $apiFactory, UserSession $userSession)
    {
        $this->apiFactory = $apiFactory;
        $this->userSession = $userSession;
    }


    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     * @throws \Exception
     */
    public function loadUserByUsername($username)
    {
        // Load a User object from your data source or throw UsernameNotFoundException.
        // The $username argument may not actually be a username:
        // it is whatever value is being returned by the getUsername()
        // method in your User class.

//        $userApi = $this->apiFactory->getUserApi();
//        $this->userSession->

        // We'll see if we need this in the end.

        throw new \Exception('implement loadUserByUsername() inside '.__FILE__);
    }



    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.

        if ( ! $user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $user->updateFromUserSession($this->userSession);

        return $user;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class)
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Upgrades the encoded password of a user, typically for using a better hash algorithm.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        // When encoded passwords are in use, this method should:
        // 1. persist the new password in the user storage
        // 2. update the $user object with $user->setPassword($newEncodedPassword);
        throw new \Exception('implement upgradePassword() inside '.__FILE__);
    }
}
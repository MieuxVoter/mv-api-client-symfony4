<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Class User
 * @package App\Entity
 */
class User implements UserInterface
{

    /** @var string $username */
    private $username;

    /** @var string|null $api_token */
    private $api_token;

    ///
    ///

    /**
     * @return string|null
     */
    public function getApiToken() : ?string
    {
        return $this->api_token;
    }

    /**
     * @param string|null $api_token
     */
    public function setApiToken(?string $api_token): void
    {
        $this->api_token = $api_token;
    }

    /**
     * Returns the roles granted to the user.
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * Initially returns the password used to authenticate the user.
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * But!
     * We store no password, and we do not use this in the login guard.
     * Still, the Security internals use this, amongst other to see if the User changed.
     * They check if the password changed **before** checking the username.
     * See symfony/security-core/Authentication/Token/AbstractToken.php line 312
     *
     * So, we're returning the API token if there's one.
     * We could also return a static string, perhaps?  It worked in preliminary tests.
     *
     * @return string|null The encoded password if any
     * @throws \Exception
     */
    public function getPassword()
    {
        return $this->getApiToken();
//        throw new \Exception("The method User#getPassword() was used. It should not.");
//        return 'The method getPassword() was used. It should not.';
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }


    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // FIXME: test this (and mem0)
//        $this->api_token = null;
    }


    public function isClaimed(): bool
    {
        return true; // todo: nope ; use a service, not the user, since it does not have access to DiC ?
    }


    /**
     * Get a clone of this User, without the API token set.
     * This is used in twig templates and translation strings,
     * who are prone to injection vulnerabilities because of their nature,
     * because we use them often to interpolate data coming from userland.
     * Therefore, it's usually best to provide them with watered-down versions of our data.
     *
     * @return $this
     */
    public function getSafeClone() : self
    {
        $user = clone $this;
        $user->setApiToken(null);
        return $user;
    }


    /**
     * @deprecated
     * @return $this
     */
    public function getWithoutToken() : self
    {
        return $this->getSafeClone();
    }
}

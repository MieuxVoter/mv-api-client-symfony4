<?php

namespace App\Entity;

//use Doctrine\ORM\Mapping as ORM;
//use MsgPhp\User\User as BaseUser;
//use MsgPhp\User\UserId;
//use MsgPhp\Domain\Event\DomainEventHandler;
//use MsgPhp\Domain\Event\DomainEventHandlerTrait;
//use MsgPhp\User\Credential\EmailPassword;
//use MsgPhp\User\Model\EmailPasswordCredential;
//use MsgPhp\User\Model\ResettablePassword;
//use MsgPhp\User\Model\RolesField;
use Symfony\Component\Security\Core\User\UserInterface;

// * @ORM\Entity()
/**
 * We don't use this anymore.  Best disable it entirely.
 * Unless we figure out a way of using it without Doctrine?
 *
 */


class User
    implements UserInterface
    //extends BaseUser implements DomainEventHandler
{
//    use DomainEventHandlerTrait;
//    use EmailPasswordCredential;
//    use ResettablePassword;
//    use RolesField;
//
//    /** @ORM\Id() @ORM\GeneratedValue() @ORM\Column(type="msgphp_user_id", length=191) */
//    private $id;

    private $api_token;

//
//    public function __construct(UserId $id, string $email, string $password)
//    {
//        $this->id = $id;
//        $this->credential = new EmailPassword($email, $password);
//    }
//
//    public function getId(): UserId
//    {
//        return $this->id;
//    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        return $this->api_token;
    }

    /**
     * @param mixed $api_token
     */
    public function setApiToken($api_token): void
    {
        $this->api_token = $api_token;
    }


    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
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
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string|null The encoded password if any
     */
    public function getPassword()
    {
        return 'nope';
        // TODO: Implement getPassword() method.
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
        // TODO: Implement getSalt() method.
        return null;
    }

    private $username;

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
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }
}

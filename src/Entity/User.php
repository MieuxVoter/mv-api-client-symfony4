<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\User as BaseUser;
use MsgPhp\User\UserId;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\User\Credential\EmailPassword;
use MsgPhp\User\Model\EmailPasswordCredential;
use MsgPhp\User\Model\ResettablePassword;
use MsgPhp\User\Model\RolesField;

/**
 * @ORM\Entity()
 */
class User extends BaseUser implements DomainEventHandler
{
    use DomainEventHandlerTrait;
    use EmailPasswordCredential;
    use ResettablePassword;
    use RolesField;

    /** @ORM\Id() @ORM\GeneratedValue() @ORM\Column(type="msgphp_user_id", length=191) */
    private $id;

    public function __construct(UserId $id, string $email, string $password)
    {
        $this->id = $id;
        $this->credential = new EmailPassword($email, $password);
    }

    public function getId(): UserId
    {
        return $this->id;
    }
}

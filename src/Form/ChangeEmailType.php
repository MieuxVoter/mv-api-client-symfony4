<?php

declare(strict_types=1);

namespace App\Form;

use MsgPhp\User\Infrastructure\Validator\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'label' => 'label.username',
            'constraints' => [new NotBlank(), new Email(), new UniqueUsername()],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
//use MsgPhp\User\Infrastructure\Form\Type\HashedPasswordType as PasswordType; // should we?
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 *
 *
 * UNUSED -- PERHAPS LATER
 *
 *
 *
 * Class ChangePasswordType
 * @package App\Form
 */
final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'form.register.password.label',
                'constraints' => new NotBlank(),
            ])
            ->add('password_confirm', PasswordType::class, [
                'label' => 'form.register.password_confirm.label',
                'constraints' => new NotBlank(),
            ])
//            ->add('password', PasswordType::class, [
//                'password_confirm' => true,
//                'password_options' => [
//                    'label' => 'label.password',
//                    'constraints' => new NotBlank(),
//                ],
//                'password_confirm_options' => [
//                    'label' => 'label.confirm_password',
//                ],
//            ])
            ->add('current', PasswordType::class, [
                'label' => 'label.current_password',
                'constraints' => new UserPassword(),
//                'password_options' => ['constraints' => new UserPassword()],
                'mapped' => false,
            ])
        ;
    }
}

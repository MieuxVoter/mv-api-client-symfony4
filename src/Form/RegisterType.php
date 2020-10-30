<?php

declare(strict_types=1);

namespace App\Form;

use MsgPhp\User\Infrastructure\Form\Type\HashedPasswordType;
use MsgPhp\User\Infrastructure\Validator\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'form.register.email.label',
                'constraints' => [new NotBlank(), new Email(), new UniqueUsername()],
            ])
            ->add('password', HashedPasswordType::class, [
                'password_confirm' => true,
                'password_options' => [
                    'label' => 'form.register.password.label',
                    'constraints' => new NotBlank()
                ],
                'password_confirm_options' => [
                    'label' => 'form.register.password_confirm.label',
                ],
            ])
            ->add('cookie_consent', CheckboxType::class, [
                'label' => 'form.register.cookie_consent.label',
                'required' => true,
            ])
            ->add('eula_agreement', CheckboxType::class, [
                'label' => 'form.register.eula_agreement.label',
                'required' => true,
            ])
        ;
    }
}
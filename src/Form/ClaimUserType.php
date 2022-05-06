<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;


final class ClaimUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Helps with password managers, but it not meant to be written to, at least not here.
            // Could be hidden instead of disabled for the same effect.
            // Showing it yields dialogue opportunities!
            ->add('username', TextType::class, [
                'label' => 'form.register.username.label',
                'attr' => [
                    // Trying to overwrite the username could be a honeypot adventure.
                    // We could also "sell" changing your username (premium, patrons, merit, etc.).
                    // "Disabled" causes the form to lose the value on password mismatch
//                    'disabled' => 'disabled',
                    // So we use "readonly", less pretty CSS, but we'll tweak it later.
                    'readonly' => true,
//                    'class' => '',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => [
                    'attr' => [
//                        'class' => 'password-field',
                    ],
                ],
                'required' => true,
                'first_options'  => ['label' => 'form.register.password.label'],
                'second_options' => ['label' => 'form.register.password_confirm.label'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.register.email.label',
                'constraints' => new Email(),
                'required' => false,
                'attr' => [
                    'class' => '',
//                    'class' => 'input-lg', // nope?
                ],
            ])
            ->add('cookie_consent', CheckboxType::class, [
                'label' => 'form.register.cookie_consent.label',
                'required' => true,
            ])
            ->add('eula_agreement', CheckboxType::class, [
                'label' => 'form.register.eula_agreement.label',
                'label_attr' => ['class' => ''],
                'required' => true,
            ])
        ;

        $builder->add('save',SubmitType::class, [
            'label' => 'action.claim.label',
            'attr' => [
                'class' => 'btn btn-xlg btn-primary float-right',
            ],
        ]);
    }
}

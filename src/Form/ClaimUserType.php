<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;


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
                    // This field could be a honeypot adventure.
                    // We could also "sell" changing your username (premium, patrons, merit, etc.).
                    'disabled' => 'disabled',
//                    'class' => '',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'form.register.password.label',
                'constraints' => new NotBlank(),
            ])
            ->add('password_confirm', PasswordType::class, [
                'label' => 'form.register.password_confirm.label',
                'constraints' => new NotBlank(), // todo: check against password, in here or in controller
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
                'label_attr' => ['class' => 'form-switch'],
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

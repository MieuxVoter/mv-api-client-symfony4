<?php

namespace App\Form;

use App\Entity\Poll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scopes = [
            'form.poll.scopes.public.name' => 'public',
            'form.poll.scopes.unlisted.name' => 'unlisted',
            'form.poll.scopes.private.name' => 'private',
        ];
        $builder
            ->add('subject', TextType::class, [
                'required' => true,
            ])
            ->add('scope', ChoiceType::class, [
                'choices' => $scopes,
                'multiple' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Create Poll'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Poll::class,
        ]);
    }
}


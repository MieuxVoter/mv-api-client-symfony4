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
    const OPTION_AMOUNT_OF_PROPOSALS = 'amount_of_proposals';
    const OPTION_AMOUNT_OF_GRADES = 'amount_of_grades';

    const DEFAULT_AMOUNT_OF_PROPOSALS = 5;
    const DEFAULT_AMOUNT_OF_GRADES = 7;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scopes = [
            'form.poll.scopes.public.name' => 'public',
            'form.poll.scopes.unlisted.name' => 'unlisted',
            'form.poll.scopes.private.name' => 'private',
        ];
        $builder
            ->add('subject', TextType::class, [
                'required' => false, // let the the API handle it, so we can use the "more" buttons
                'empty_data' => '',
            ])
            ->add('scope', ChoiceType::class, [
                'choices' => $scopes,
                'multiple' => false,
            ])
        ;

        for ($i = 0; $i < $options[self::OPTION_AMOUNT_OF_PROPOSALS]; $i++) {
            $builder->add(
                sprintf("proposal_%02d_title", $i),
                TextType::class,
                [
                    'required' => ($i < 2), // 2 = minimum amount of proposals required
                    'property_path' => "proposals[$i]",
                ]
            );

        }

        $builder->add('save',SubmitType::class, [
            'label' => 'button.create_poll',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Poll::class,
            self::OPTION_AMOUNT_OF_PROPOSALS => self::DEFAULT_AMOUNT_OF_PROPOSALS,
            self::OPTION_AMOUNT_OF_GRADES => self::DEFAULT_AMOUNT_OF_GRADES,
        ]);
    }
}


<?php

namespace App\Form;

use App\Entity\Poll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;


class PollType extends AbstractType
{

    const OPTION_AMOUNT_OF_PROPOSALS = 'amount_of_proposals';
    const OPTION_AMOUNT_OF_GRADES = 'amount_of_grades';

    const MINIMUM_AMOUNT_OF_PROPOSALS = 2;
    const MAXIMUM_AMOUNT_OF_PROPOSALS = 250;
    const DEFAULT_AMOUNT_OF_PROPOSALS = 5;
    const DEFAULT_AMOUNT_OF_GRADES = 7;

    ///
    ///

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    ///
    ///

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scopes = [
            'form.poll.scopes.public.name' => Poll::SCOPE_PUBLIC,
            'form.poll.scopes.unlisted.name' => Poll::SCOPE_UNLISTED,
            'form.poll.scopes.private.name' => Poll::SCOPE_PRIVATE,
        ];
        $presets = [];
        foreach (Poll::GRADING_PRESETS as $preset) {
            $presetLabel = "${preset}.name";

            $presets[$presetLabel] = $preset;
        }

        $isLoggedIn = (null !== $this->security->getUser());

        $builder
            ->add(self::OPTION_AMOUNT_OF_PROPOSALS, HiddenType::class, [
                'data' => $options[self::OPTION_AMOUNT_OF_PROPOSALS],
            ]);

        $builder
            ->add('subject', TextType::class, [
                'required' => false, // let the the API handle it, so we can use the "more" buttons
                'empty_data' => '',
                'label' => 'form.poll.subject.label',
                'attr' => [
                    'placeholder' => 'form.poll.subject.placeholder',
                    'title' => 'form.poll.subject.title',
                ],
            ]);

        $builder
            ->add('scope', ChoiceType::class, [
                'choices' => $scopes,
                'multiple' => false,
                'label' => 'form.poll.scope.label',
                'attr' => [
                    'title' => 'form.poll.scope.title',
                ],
                'choice_attr' => function($val, $key, $index) use ($isLoggedIn) {
                    $attr = [];

                    $disabled = false;
                    if ((! $isLoggedIn) && (Poll::SCOPE_PRIVATE === $val)) {
                        $disabled = true; // private is only for logged-in users, for now
                        $attr = array_merge($attr, ['title' => 'form.poll.scope.private_requires_login']);
                    }
                    if ($disabled) {
                        $attr = array_merge($attr, ['disabled' => 'disabled']);
                    }

                    return $attr;
                },
            ]);

        $builder
            ->add('grading_preset', ChoiceType::class, [
                'choices' => $presets,
                'multiple' => false,
                'translation_domain' => 'grades',
                'label' => 'grading_preset',
            ]);

        for ($i = 0; $i < $options[self::OPTION_AMOUNT_OF_PROPOSALS]; $i++) {
            $required = ($i < 2);  // 2 = minimum amount of proposals required
            $builder->add(
                sprintf("proposal_%02d_title", $i),
                TextType::class,
                [
                    'required' => $required,
                    'property_path' => "proposals[$i]",
                    'label' => 'form.poll.proposal.label',
                    'label_translation_parameters' => [
                        'id' => \num2alpha($i),
                    ],
                    'attr' => [
                        'title' => 'form.poll.proposal.title.'.($required?'required':'optional'),
                        'placeholder' => 'form.poll.proposal.placeholder.'.($required?'required':'optional'),
                    ],
                ]
            );
        }

        $builder->add('moreProposals',SubmitType::class, [
            'label' => 'button.more_proposals',
        ]);

        $builder->add('save',SubmitType::class, [
            'label' => 'button.create_poll',
            'attr' => [
                'class' => 'btn btn-primary float-right',
            ],
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


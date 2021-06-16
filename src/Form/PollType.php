<?php

namespace App\Form;

use App\Entity\Poll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

//        $isLoggedIn = (null !== $this->security->getUser()); // perhaps not the correct API

        $builder
            ->add(self::OPTION_AMOUNT_OF_PROPOSALS, HiddenType::class, [
                'data' => $options[self::OPTION_AMOUNT_OF_PROPOSALS],
            ]);

        $builder
            ->add('subject', TextType::class, $this->buildSubjectOptions());

        $builder
            ->add('scope', ChoiceType::class, [
                'choices' => $scopes,
                'multiple' => false,
                'label' => 'form.poll.scope.label',
                'attr' => [
                    'title' => 'form.poll.scope.title',
                ],
//                'choice_attr' => function($val, $key, $index) use ($isLoggedIn) {
//                    $attr = [];
//
//                    $disabled = false;
//                    if ((! $isLoggedIn) && (Poll::SCOPE_PRIVATE === $val)) {
//                        $disabled = true; // private is only for logged-in users, for now
//                        $attr = array_merge($attr, ['title' => 'form.poll.scope.private_requires_login']);
//                    }
//                    if ($disabled) {
//                        $attr = array_merge($attr, ['disabled' => 'disabled']);
//                    }
//
//                    return $attr;
//                },
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
                $this->buildProposalOptions($i, $required)
            );
        }

        $builder->add('moreProposals',SubmitType::class, [
            'label' => 'button.more_proposals',
            'attr' => [
                'class' => 'btn btn-add',
            ],
        ]);

        $builder->add('save',SubmitType::class, [
            'label' => 'button.create_poll',
            'attr' => [
                'class' => 'btn btn-primary float-right',
            ],
        ]);

        // Autofocus shenanigans, we have to hook an event to get the $poll with actual data
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var Poll $poll */
            $poll = $event->getData();
            $form = $event->getForm();

            $autofocusWasSet = false;

            if (empty($poll->getSubject())) {
                $form->add('subject', TextType::class, $this->buildSubjectOptions(true));
                $autofocusWasSet = true;
            }

            $proposals = $poll->getProposals();
            for ($i = 0; $i < $options[self::OPTION_AMOUNT_OF_PROPOSALS]; $i++) {
                if ($autofocusWasSet) break;
                $doit = false;
                if ($i >= count($proposals)) {
                    $doit = true;
                } else {
                    if (empty($proposals[$i])) {
                        $doit = true;
                    }
                }
                if ($doit) {
                    $required = ($i < 2);  // 2 = minimum amount of proposals required
                    $form->add(
                        sprintf("proposal_%02d_title", $i),
                        TextType::class,
                        $this->buildProposalOptions($i, $required, true)
                    );
                    $autofocusWasSet = true;
                }
            }
        });
    }


    protected function buildSubjectOptions($autofocus = false)
    {
        return [
            'required' => false, // let the the API handle it, so we can use the "more proposals" button
            'empty_data' => '',
            'label' => 'form.poll.subject.label',
            'attr' => [
                'title' => 'form.poll.subject.title',
                'placeholder' => 'form.poll.subject.placeholder',
            ] + (($autofocus) ? ['autofocus' => 'autofocus'] : []),
        ];
    }

    protected function buildProposalOptions($i, $required = false, $autofacus = false)
    {
        return [
            'required' => $required,
            'property_path' => "proposals[$i]",
            'label' => 'form.poll.proposal.label',
            'label_translation_parameters' => [
                'id' => \num2alpha($i),
            ],
            'attr' => [
                'title' => 'form.poll.proposal.title.'.($required?'required':'optional'),
                'placeholder' => 'form.poll.proposal.placeholder.'.($required?'required':'optional'),
            ] + (($autofacus) ? ['autofocus' => 'autofocus'] : []),
        ];
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


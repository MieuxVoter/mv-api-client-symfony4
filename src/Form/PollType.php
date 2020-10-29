<?php

namespace App\Form;

use App\Entity\Poll;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Contracts\Translation\TranslatorInterface;

class PollType extends AbstractType
{
    const OPTION_AMOUNT_OF_PROPOSALS = 'amount_of_proposals';
    const OPTION_AMOUNT_OF_GRADES = 'amount_of_grades';

    const DEFAULT_AMOUNT_OF_PROPOSALS = 5;
    const DEFAULT_AMOUNT_OF_GRADES = 7;

    // TBD: I prefer keeping the "missing translations" clean
//    /** @var TranslatorInterface */
//    protected $translator;
//
//    /**
//     * PollType constructor.
//     * @param TranslatorInterface $translator
//     */
//    public function __construct(TranslatorInterface $translator)
//    {
//        $this->translator = $translator;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scopes = [
            'form.poll.scopes.public.name' => 'public',
            'form.poll.scopes.unlisted.name' => 'unlisted',
            'form.poll.scopes.private.name' => 'private',
        ];
        $presets = [];
        foreach (Poll::GRADING_PRESETS as $preset) {
            $presetLabel = "${preset}.name";
//            $presetLabel = $this->translator->trans("grading.${preset}.name");

            $presets[$presetLabel] = $preset;
        }

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
            ->add('grading_preset', ChoiceType::class, [
                'choices' => $presets,
                'multiple' => false,
                'translation_domain' => 'grades',
                'label' => 'entity.poll.grading_preset',
            ]);

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

        $builder->add('moreProposals',SubmitType::class, [
            'label' => 'button.more_proposals',
        ]);

        $builder
            ->add('scope', ChoiceType::class, [
                'choices' => $scopes,
                'multiple' => false,
                'label' => 'form.poll.scope.label',
                'attr' => [
                    'title' => 'form.poll.scope.title',
                ],
            ]);

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


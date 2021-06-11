<?php

namespace App\Form;

use App\Entity\Ballot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class BallotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $i = 0;
        foreach ($options['proposals'] as $proposal) {

            $choices = [];
            foreach ($options['grades'] as $grade) {
                $choices[$grade->getName()] = $grade->getUuid();
            }

            $builder
                ->add(sprintf('judgment_%02d', $i), ChoiceType::class, [
                    'label' => $proposal->getTitle(),
                    'choices' => $choices,
                    'multiple' => false,
                    'expanded' => true,
                    'data' => ($options['grades'][0])->getUuid(),
                    'row_attr' => [
                        'class' => 'form-radio-grade-check',
                    ],
                    'attr' => [
                        'class' => 'form-radio-grade',
                    ],
                    'property_path' => "judgments[".$proposal->getUuid()."]",
    //                'translation_domain' => 'grades',
                ]);
            $i++;
        }


        $builder->add('save',SubmitType::class, [
            'label' => 'button.submit_ballot',
            'attr' => [
                'class' => 'btn btn-xlg btn-primary float-right',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ballot::class,
            'grades' => [],
            'proposals' => [],
        ]);
    }
}

<?php

namespace A2Global\CRMBundle\Form;

use A2Global\CRMBundle\Entity\EntityField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityFieldTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
//            ->add('type', ChoiceType::class, [
//                'choices' => [
//                    'String' => 'string',
//                    'Digit' => 'integer',
//                ]
//            ]
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EntityField::class,
        ]);
    }
}

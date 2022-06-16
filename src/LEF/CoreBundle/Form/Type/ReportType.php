<?php
// src/LEF/CoreBundle/Form/Type/ReportType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('severity', ChoiceType::class, array(
            'choices' => array(
                'spam' => 1,
                'sexual' => 2,
                'violence' => 3,
                'harass' => 4
            ),
            'choice_label' => function($value, $key, $index) {
                return 'form.report.' . $key;
            },
            'expanded' => true,
            'label' => false
        ));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\CoreBundle\Entity\Report',
            'translation_domain' => 'form'
        ));
    }
}
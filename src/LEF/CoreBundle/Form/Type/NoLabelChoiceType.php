<?php
// src/LEF/CoreeBundle/Form/Type/NoLabelChoiceType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class NoLabelChoiceType extends AbstractType {
    public function getParent() {
        return ChoiceType::class;
    }
    
    public function getBlockPrefix() {
        return 'no_label_choice';
    }
}

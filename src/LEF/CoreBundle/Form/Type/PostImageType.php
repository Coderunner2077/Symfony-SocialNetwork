<?php
// src/LEF/CoreBundle/Form/Type/PostImageType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class PostImageType extends AbstractType {
    public function getBlockPrefix() {
        return 'lef_image';
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefault('label_src', '');
        $resolver->setAllowedTypes('label_src', 'string');
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options) {
        $view->vars = array_merge($view->vars, array(
            'label_src' => $options['label_src']));
    }
    
    public function getParent() {
        return ImageType::class;
    }
}
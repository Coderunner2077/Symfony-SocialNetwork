<?php
// src/LEF/CoreeBundle/Form/Type/TextareaImageType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TextareaImageType extends AbstractType {
    public function getParent() {
        return TextareaType::class;
    }
    
    public function getBlockPrefix() {
        return 'textarea_image';
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'image_attr' => array(
                'src' => null,
                'alt' => null
            )
        ));
        
        $resolver->setAllowedTypes('image_attr', 'array');
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options) {
        //throw new \RuntimeException('options : ' . $options['label_src']);
        $view->vars = array_replace($view->vars, array('image_attr' => $options['image_attr']));
    }
}

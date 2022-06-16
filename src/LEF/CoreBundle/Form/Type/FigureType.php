<?php
// src/LEF/CoreBundle/Form/Type/FigureType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FigureType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('alt', TextType::class, array(
            'label' => 'form.image.legend' 
        ));
    }
    
    public function getParent() {
        return ImageType::class;
    }
}
<?php
// src/LEF/CoreeBundle/Form/Type/ImageType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImageType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $opions) {
        $builder->add('file', FileType::class, array('error_bubbling' => true));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\CoreBundle\Entity\Image',
            'translation_domain' => 'form'
        ));
    }
}

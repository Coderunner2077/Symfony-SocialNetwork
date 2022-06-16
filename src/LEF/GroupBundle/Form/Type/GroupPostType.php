<?php
// src/LEF/GroupBundle/Form/Type/GroupPostType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class GroupPostType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('content', TextareaType::class, array(
            'attr' => array(
                'placeholder' => 'form.group_post.add_post',
                'maxlength' => 255
            ),
            'help' => 'range.max._255'
        ))
                ->add('publicPost', ChoiceType::class, array(
                    'choices' => array(
                        'maybe' => null,
                        'public_post' => true,
                        'restricted_post' => false
                    ),
                    'choice_label' => function($value, $key, $index) {
                        return $value === null ? 'form.choose.is_post_public' : 'form.' . $key;
                    },
                    'preferred_choices' => function($value, $key, $index) {
                        if($value === null)
                            return $key;
                    },
                    'label' => 'form.visibility'
                ))
                ->add('reset', ResetType::class, array('label' => 'form.reset'))
                ->add('save', SubmitType::class, array(
                    'label' => 'form.group_post.add'
                ));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\GroupBundle\Entity\GroupPost',
            'translation_domain' => 'form'
        ));
    } 
}
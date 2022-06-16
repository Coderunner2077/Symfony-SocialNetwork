<?php
// src/LEF/PostBundle/Form/Type/SearchPostType.php

namespace LEF\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use LEF\CoreBundle\Form\Type\ListType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchPostType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('mostLiked', ListType::class, array(
                    'choices' => array(
                        'most_recent' => false,
                        'most_liked' => true
                    ),
                    'choice_label' => function ($choiceValue, $key, $value) {
                        return 'form.search.' . $key;
                    }
                ))
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $search = $event->getData();
                    if(empty($search))
                        return;
                    $form = $event->getForm();
                    $form->add('input', SearchType::class, array(
                        'attr' => array('placeholder' => 'form.search.hashtag'),
                        'data' => $search->getInput()
                    ));
                });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\CoreBundle\Component\Search\Object\SearchPost',
            'translation_domain' => 'form',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'search_item'
        ));
    }
}


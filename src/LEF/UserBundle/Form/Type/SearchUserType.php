<?php
// src/LEF/UserBundle/Form/Type/SearchUserType.php

namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use LEF\CoreBundle\Form\Type\ListType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchUserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $search = $event->getData();
                    if(empty($search))
                        return;
                    $form = $event->getForm();
                    $form->add('input', SearchType::class, array(
                        'attr' => array('placeholder' => 'form.search.user_placeholder'),
                        'data' => $search->getInput(),
                        'help' => 'form.search.user_help'
                    ));
                });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\CoreBundle\Component\Search\Object\SearchUser',
            'translation_domain' => 'form',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'search_item'
        ));
    }
}


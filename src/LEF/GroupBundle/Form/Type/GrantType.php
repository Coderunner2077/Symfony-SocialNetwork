<?php
// src/LEF/GroupBundle/Form/Type/GrantType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\GroupBundle\Component\Vacancy\VacancyManager; 
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrantType extends AbstractType {
    protected $vacancyManager;
    
    public function __construct(VacancyManager $vacancyManager) { 
        $this->vacancyManager = $vacancyManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $privilege = $event->getData();
            if(empty($privilege))
                return;
            $choices = $this->vacancyManager->provideChoices(null, null, $privilege);
            $form->add('role', ChoiceType::Class, array(
                'choices' => $choices,
                'label' => 'form.role',
                'mapped' => false
            ));
        })
        ->add('grant', SubmitType::class, array('label' => 'form.grant'));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\GroupBundle\Entity\MemberPrivilege',
            'translation_domain' => 'form'
        ));
    }
    
}
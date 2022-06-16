<?php
// src/LEF/GroupBundle/Form/Type/GroupEventType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class GroupEventType extends AbstractType {     
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('electionAt', DateType::class, array(
            'data' => new \DateTime('+30 days')
        ))
                ->add('reset', ResetType::class, array('label' => 'form.reset'))
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $groupEvent = $event->getData();
                    $form = $event->getForm();
                    
                    if(empty($groupEvent))
                        return;
                   
                    if(empty($groupEvent->getId()))
                        $form->add('save', SubmitType::class, array('label' => 'form.election.add'));
                    else 
                        $form->add('save', SubmitType::class, array('label' => 'form.election.modify'));
                  
                    $form->add('group', TextType::class, array(
                        'data' => $groupEvent->getGroup()->getName(),
                        'disabled' => true,
                        'mapped' => false
                    ));
                });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\GroupBundle\Entity\GroupEvent',
            'translation_domain' => 'form'
        ));
    }
}
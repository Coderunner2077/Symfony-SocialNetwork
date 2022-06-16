<?php
// src/LEF/CoreBundle/Form/Type/ContactType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('email', EmailType::class)
                ->add('message', TextareaType::class, array('label' => 'form.message'))
                ->add('send', SubmitType::class, array('label' => 'form.send'))
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $contact = $event->getData();
                    $form = $event->getForm();
                    if(empty($contact->getId()))
                        $form->add('name', TextType::class)
                             ->add('email', EmailType::class);
                    else 
                        $form->add('name', TextType::class, array(
                            'disabled' => true
                             ))
                             ->add('email', EmailType::class, array(
                                 'disabled' => true
                             ));
                });
    }
    
   public function configureOptions(OptionsResolver $resolver) {
       $resolver->setDefaults(array(
           'data_class' => 'LEF\CoreBundle\Entity\Contact',
           'translation_domain' => 'form'
       ));
   }
}
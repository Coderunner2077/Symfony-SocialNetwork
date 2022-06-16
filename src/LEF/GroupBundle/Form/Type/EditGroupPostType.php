<?php
// src/LEF/GroupBundle/Form/Type/EditGroupPostType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\CoreBundle\Form\Type\TextareaImageType;
use Symfony\Component\Form\Exception\LogicException;


class EditGroupPostType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $post = $event->getData();
            $form = $event->getForm();
            if(empty($post))
                return;
            $author = $post->getAuthor();
            if(empty($author))
                throw new LogicException('exception.logic.lefgroup.form');
            $form->add('content', TextareaImageType::class, array(
                'image_attr' => array(
                    'src' => $author->avatarSrc(),
                    'alt' => 'content'
                ),
                'attr' => array(
                    'maxlength' => 255
                ),
                'help' => 'range.max._255'
            ));
        });
           
        $builder->add('reset', ResetType::class, array('label' => 'form.cancel'))
                ->add('save', SubmitType::class);
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\GroupBundle\Entity\GroupPost',
            'translation_domain' => 'form'
        ));
    } 
}
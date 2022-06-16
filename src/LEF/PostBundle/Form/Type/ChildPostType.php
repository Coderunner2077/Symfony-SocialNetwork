<?php
// src/LEF/PostBundle/Form/Type/ChildPostType.php

namespace LEF\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\CoreBundle\Form\Type\TextareaImageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildPostType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
       $builder->add('repost', CheckboxType::class, array('required' => false))
                ->add('reset', ResetType::class, array('label' => 'form.reset'))
                ->add('save', SubmitType::class, array(
                    'label' => 'form.post.add'
               ))
               ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
           $post = $event->getData();
           $form = $event->getForm();
           if(empty($post))
               return;
           $form->add('content', TextareaImageType::class, array(
                   'image_attr' => array(
                       'src' => $post->getAuthor()->avatarSrc(),
                       'alt' => 'content'
                   ),
                   'attr' => array(
                       'placeholder' => 'form.comment.add',
                        'maxlength' => 255
                   ),
                   'help' => 'range.max._255'
               ));
           
           if($post->getLvl() > 1) 
               $form->remove('repost');
       });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\PostBundle\Entity\Post',
            'translation_domain' => 'form'
        ));
    } 

   
}
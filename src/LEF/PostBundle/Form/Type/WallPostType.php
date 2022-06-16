<?php
// src/LEF/PostBundle/Form/Type/WallPostType.php

namespace LEF\PostBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\CoreBundle\Form\Type\TextareaImageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LEF\CoreBundle\Form\Type\PostImageType;

class WallPostType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
       $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
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
                       'placeholder' => 'form.post.add_public',
                       'maxlength' => 255
                   ),
               'help' => 'range.max._255'
               ));
       })
       ->add('image', PostImageType::class, array('label' => 'form.image.add',
           'label_src' => 'add_image.jpg', 'required' => false,  'error_bubbling' => false
       ))
       ->add('wall', HiddenType::class, array('required' => false, 'mapped' => false))
       ->add('reset', ResetType::class, array('label' => 'form.reset'))
       ->add('save', SubmitType::class, array(
           'label' => 'form.post.add'
       ));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\PostBundle\Entity\Post',
            'translation_domain' => 'form'
        ));
    } 
}
<?php
// src/LEF/GroupBundle/Form/Type/WallGroupPostType.php

namespace LEF\GroupBundle\Form\Type;

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

class WallGroupPostType extends AbstractType {
    protected $authContext;
    
    public function __construct(AuthenticationContext $authContext) {
        $this->authContext = $authContext;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
       $builder->add('image', PostImageType::class, array('label' => 'form.image.add',
           'label_src' => 'add_image.jpg', 'required' => false,  'error_bubbling' => false
       ))
       ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
           $post = $event->getData();
           $form = $event->getForm();
           if(empty($post))
               return;
           $form->add('content', TextareaImageType::class, array(
                   'image_attr' => array(
                       'src' => $this->authContext->getCurrentUser()->avatarSrc(),
                       'alt' => 'content'
                   ),
                   'attr' => array(
                       'placeholder' => 'form.group_post.add_'. ($post->isPublic() ? 'public' : 'restricted'),
                       'maxlength' => 255
                   ),
                   'help' => 'range.max._255'
               ));
           $form->add('is_public', HiddenType::class, array(
               'data' => $post->isPublic() ? 'true' : 'false',
               'mapped' => false,
               'required' => false
           ));
       })
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
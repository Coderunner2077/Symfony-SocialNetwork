<?php
// src/LEF/ArticleBundle/Form/Type/ChildCommentType.php

namespace LEF\ArticleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LEF\ArticleBundle\Repository\ArticleRepository;
use LEF\CoreBundle\Form\Type\QueryType;
use LEF\CoreBundle\Form\Type\TextareaImageType;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType {
    protected $authContext;
    
    public function __construct(AuthenticationContext $authContext) {
        $this->authContext = $authContext;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('reset', ResetType::class, array('label' => 'form.reset'))
                ->add('save', SubmitType::class, array(
                    'label' => 'form.comment.add'
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
                   'placeholder' => 'form.comment.add_placeholder',
                   'maxlength' => 255
               ),
               'help' => 'range.max._255'
          ));
        });
    }
    
    public function setDefaults(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\ArticleBundle\Entity\Comment',
            'translation_domain' => 'form'
        ));
    }
}
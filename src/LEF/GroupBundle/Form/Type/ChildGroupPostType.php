<?php
// src/LEF/GroupBundle/Form/Type/ChildGroupPostType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\GroupBundle\Repository\MemberPrivilegeRepository;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use LEF\GroupBundle\Security\AlternativeGroupChecker;
use LEF\CoreBundle\Form\Type\TextareaImageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChildGroupPostType extends AbstractType {
    protected $altGroupChecker;
    protected $authContext;
    protected $translator;
    
    public function __construct(AuthenticationContext $authContext, AlternativeGroupChecker $altGroupChecker,
            TranslatorInterface $translator) {
        $this->authContext = $authContext;
        $this->altGroupChecker = $altGroupChecker;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
       $builder->add('repost', CheckboxType::class, array('required' => false))
                ->add('reset', ResetType::class, array('label' => 'form.reset'))
                ->add('save', SubmitType::class, array(
                    'label' => 'form.group_post.add'
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
                       'placeholder' => 'form.comment'. ($post->isPublic() ? '.public' : '') . '.add',
                       'maxlength' => 255
                   ),
                   'help' => 'range.max._255'
               ));
           
           if($post->getLvl() > 1 || $post->isPublic() === false 
               || $this->altGroupChecker->hasAlternative(PrivilegeBitmasks::POST, $post->getGroup()) === false) 
               $form->remove('repost');
       });
       
           
       $builder->get('repost')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
           $isRepost = $event->getForm()->getData();
           $groupPost = $event->getForm()->getParent()->getData();
           $author = $groupPost->getAuthor();
           $groupId = $groupPost->getGroup()->getId();
   
           if($isRepost === true) 
               $event->getForm()->getParent()->add('group', EntityType::class, array(
                   'class' => 'LEFGroupBundle:MemberPrivilege',
                   'choice_label' => function($privilege) {
                       return $privilege->getGroup()->getName();
                   },
                   'query_builder' => function(MemberPrivilegeRepository $rep) use ($author, $groupId) {
                       return $rep->queryBuilderForGroups($author->getId(), PrivilegeBitmasks::POST, $groupId);
                   },
                   'preferred_choices' => function($value, $key, $index) {
                       if(empty($value))
                           return $key;
                   }
               ));
           elseif($event->getForm()->getParent()->has('group'))
               $event->getForm()->getParent()->remove('group');     
       });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\GroupBundle\Entity\GroupPost',
            'translation_domain' => 'form'
        ));
    } 
    
    public function finishView(FormView $view, FormInterface $form, array $options) {
        if(!$form->has('group'))
            return;
        $newChoice = new ChoiceView(array(), '', 
            $this->translator->trans('form.choose.group', array(), 'form'));
        array_unshift($view->children['group']->vars['choices'], $newChoice);
    }
   
}
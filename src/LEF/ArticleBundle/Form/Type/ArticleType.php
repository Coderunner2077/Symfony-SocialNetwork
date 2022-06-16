<?php
// src/LEF/ArticleBundle/Form/Type/ArticleType.php

namespace LEF\ArticleBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use LEF\CoreBundle\Form\Type\QueryType;
use LEF\CoreBundle\Form\Type\FigureType;
use LEF\GroupBundle\Repository\MemberPrivilegeRepository;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\Translation\TranslatorInterface;

class ArticleType extends AbstractType {
    protected $authContext;
    protected $translator;
    
    public function __construct(AuthenticationContext $authContext, TranslatorInterface $translator) {
        $this->authContext = $authContext;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('title', TextType::class, array(
                    'attr' => array('maxlength' => 150),
                    'help' => 'range.max._150'
                ))
                ->add('intro', TextareaType::class, array(
                    'attr' => array('maxlength' => 255),
                    'help' => 'range.max._255'
                ))
                ->add('image', FigureType::class, array('required' => false, 'label' => 'article.image'))
                ->add('category', EntityType::class, array(
                    'class' => 'LEFArticleBundle:Category',
                    'choice_label' => function($category) {
                        return $this->translator->trans($category->getName(), array(), 'attributes');
                    }
                ))
                ->add('content', TextareaType::class)
                ->add('display', RangeType::class, array(
                    'attr' => array(
                        'min' => 0,
                        'max' => 100
                    ),
                    'help' => 'form.help.article_display',
                    'label' => 'form.article.display'
                ))
                ->add('published', CheckboxType::class, array('required' => false))
                ->add('allowComments', CheckboxType::class, array(
                    'required' => false, 'label' => 'allow_comments'))
                ->add('reset', ResetType::class, array('label' => 'form.reset'));
                 
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $article = $event->getData();
            if($article === null)
                return;
            
            $user = $this->authContext->getUser();
            
            if($user === null)
                throw new LogicException('exception.logic.form.user');
            $form = $event->getForm();
           
            
            if($article->getId() === null) {
                $form->add('group', EntityType::class, array(
                    'class' => 'LEFGroupBundle:MemberPrivilege',
                    'choice_label' => function($privilege) {
                        return $privilege->getGroup()->getName();
                    },
                    'query_builder' => function(MemberPrivilegeRepository $repository) use ($user) {
                        return $repository->queryBuilderForGroups($user->getId(), PrivilegeBitmasks::CREATE);
                    },
                    'preferred_choices' => function($value, $key, $index) {
                        if($value == null)
                            return $key;
                    },
                    'mapped' => false
                ))
                
                ->add('save', SubmitType::class, array('label' => 'form.article.add'));
            }
            else {
               $form->add('group', TextType::class, array(
                   'data' => $article->getGroup()->getName(),
                   'disabled' => true,
                   'mapped' => false
               ));
               
               if($article->getAuthor() !== $user) {
                   $event->getForm()->add('author', TextType::class, array(
                       'data' => $article->getAuthor()->getFullname(),
                       'mapped' => false,
                       'attr' => array('readonly' => 'readonly'),
                       'disabled' => true
                   ));
               }        
               
               $form->add('save', SubmitType::class, array('label' => 'form.article.edit'));
            }
        });   
    }
    
    public function finishView(FormView $view, FormInterface $form, array $options) {
        $newChoice = new ChoiceView(array(), 'choose', 
            $this->translator->trans('form.choose.category', array(), 'form'));
        $newChoice->data = null;
        array_unshift($view->children['category']->vars['choices'], $newChoice);
        
        if($form->get('group')->isDisabled()) 
            return;
        $newChoice = new ChoiceView(array(), 'choose', 
            $this->translator->trans('form.choose.group', array(), 'form'));
        array_unshift($view->children['group']->vars['choices'], $newChoice);
        
    }
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\ArticleBundle\Entity\Article',
            'translation_domain' => 'form'
        ));
    }
    
}
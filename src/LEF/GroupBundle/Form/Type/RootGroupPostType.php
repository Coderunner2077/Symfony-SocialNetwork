<?php
// src/LEF/GroupBundle/Form/Type/RootGroupPostType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\GroupBundle\Repository\MemberPrivilegeRepository;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Translation\TranslatorInterface;
use LEF\CoreBundle\Form\Type\PostImageType;

class RootGroupPostType extends AbstractType {
    protected $translator;
    
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
        ->add('image', PostImageType::class, array('label' => 'form.image.add', 
            'label_src' => 'add_image.jpg', 'required' => false))
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $groupPost = $event->getData();
            if(empty($groupPost))
                return;
            $group = $groupPost->getGroup();
            $author = $groupPost->getAuthor();
            if($group === null)
                $event->getForm()->add('group', EntityType::class, array(
                    'class' => 'LEFGroupBundle:MemberPrivilege',
                    'choice_label' => function($privilege) {
                        return $privilege->getGroup()->getName();
                    },
                    'query_builder' => function(MemberPrivilegeRepository $rep) use ($author) {
                        return $rep->queryBuilderForGroups($author->getId(), PrivilegeBitmasks::POST);
                    }
                ));
             else
                 $event->getForm()->add('group', TextType::class, array(
                    'data' => $group->getName(),
                    'mapped' => false,
                    'attr' => array('readonly' => 'readonly'),
                    'required' => false
                 ));
        });
    }
    
    public function getParent() {
        return GroupPostType::class;
    }
    
    public function finishView(FormView $view, FormInterface $form, array $options) {
        if($form->get('group')->isRequired() === false)
            return;
        $newChoice = new ChoiceView(array(), '', 
            $this->translator->trans('form.choose.group', array(), 'form'));
        array_unshift($view->children['group']->vars['choices'], $newChoice);
    }
}
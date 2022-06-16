<?php
// src/LEF/GroupBundle/Form/Type/GroupType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use LEF\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use LEF\GroupBundle\Component\Vacancy\VacancyManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\GroupBundle\AttributeNamer\GroupCategoryNamer;

class GroupType extends AbstractType {
    protected $vacancyManager;
    protected $attributeNamer;
    
    public function __construct(VacancyManager $vacancyManager, GroupCategoryNamer $namer) {
        $this->vacancyManager = $vacancyManager;
        $this->attributeNamer = $namer;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', TextType::class, array(
                    'attr' => array('maxlength' => 50),
                    'help' => 'range.max._50'
                ))
                ->add('groupCategory', EntityType::class, array(
                    'class' => 'LEFGroupBundle:GroupCategory',
                    'choice_label' => function($category) {
                        return $this->attributeNamer->provideName($category->getId());
                    },
                    'label' => 'form.group_category'
                ))
                ->add('logo', ImageType::class, array('required' => false))
                ->add('background', ImageType::class, array('required' => false))
                ->add('vacancies', ChoiceType::Class, array(
                    'choices' => $this->vacancyManager->provideChoices(),
                    'multiple' => true,
                    'mapped' => false,
                    'label' => 'form.vacancies'
                ))
                ->add('about', TextareaType::class, array(
                    'required' => false, 'label' => 'form.about'))
                ->add('democratic', ChoiceType::class, array(
                    'choices' => array(
                        'question' => null,
                        'democratic' => true,
                        'autocrat' => false
                    ),
                    'choice_label' => function($value, $key, $index) {
                        return $value === null ? 'form.choose.is_democratic' : 'form.' . $key;
                    },
                    'label' => 'form.democracy'
                ))
                ->add('reset', ResetType::class, array('label' => 'form.reset'))
                
                ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $group = $event->getData();
                    $form = $event->getForm();
                    if(empty($group->getId()))
                        $form->add('save', SubmitType::class, array('label' => 'form.group.create'));
                    else 
                        $form->add('save', SubmitType::class, array('label' => 'form.group.edit'));
                });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\GroupBundle\Entity\Group',
            'translation_domain' => 'form'
        ));
    }
}
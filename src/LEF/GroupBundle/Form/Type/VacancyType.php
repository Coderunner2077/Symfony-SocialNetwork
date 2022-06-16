<?php
// src/LEF/GroupBundle/Form/Type/VacancyType.php

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

class VacancyType extends AbstractType {
    protected $vacancyManager;
    
    public function __construct(VacancyManager $vacancyManager) {
        $this->vacancyManager = $vacancyManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('vacancies', ChoiceType::Class, array(
                    'choices' => $this->vacancyManager->provideChoices(),
                    'multiple' => true,
                    'mapped' => false,
                    'label' => 'form.vacancies'
                ))                
                ->add('save', SubmitType::class, array('label' => 'form.update'));       
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'translation_domain' => 'form'
        ));
    } 
}
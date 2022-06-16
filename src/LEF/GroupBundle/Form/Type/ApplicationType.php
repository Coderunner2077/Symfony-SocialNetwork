<?php
// src/LEF/GroupBundle/Form/Type/ChildGroupPostType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\GroupBundle\Component\Vacancy\VacancyManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType {
    protected $vacancyManager;
    
    public function __construct(VacancyManager $vacancyManager) {
        $this->vacancyManager = $vacancyManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $application = $event->getData();
            $vacancies = $application->getVacancies();
            if(empty($vacancies))
                return;
            if($application->isOffer()) {
                $form->add('offer', TextType::class, array(
                    'mapped' => false,
                    'disabled' => true,
                    'data' => $this->vacancyManager->translateMask($application->getOffer()),
                    'label' => 'form.offer'
                ));
                $vacancies = $this->vacancyManager->removeVacancy($vacancies, $application->getOffer());
            }
            
            $choices = $this->vacancyManager->provideChoices($vacancies, $application->getGroup());
            if($application->isRefused() && $application->isDemand())
                $form->add('demand', TextType::class, array(
                    'mapped' => false,
                    'disabled' => true,
                    'data' => $this->vacancyManager->translateMask($application->getDemand()),
                    'label' => 'form.demand'
                ))
                ->remove('apply');
           else
               $form->add('demand', ChoiceType::Class, array(
                'choices' => $choices,
                'label' => 'form.demand'
            ));
        })
        ->add('apply', SubmitType::class, array('label' => 'form.apply'));
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\GroupBundle\Entity\Application',
            'translation_domain' => 'form'
        ));
    }
    
}
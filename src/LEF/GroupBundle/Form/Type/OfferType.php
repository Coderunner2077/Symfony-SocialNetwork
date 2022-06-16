<?php
// src/LEF/GroupBundle/Form/Type/OfferType.php

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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use LEF\CoreBundle\Form\Type\NoLabelChoiceType;

class OfferType extends AbstractType {
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
            if($application->isDemand()) {
                $form->add('demand', TextType::class, array(
                    'mapped' => false,
                    'disabled' => true,
                    'data' => $this->vacancyManager->translateMask($application->getDemand()),
                    'label' => 'form.demand'
                ));
                $vacancies = $this->vacancyManager->removeVacancy($vacancies, $application->getDemand());
            }
            $choices = []; $choices['form.choose.offer'] = null;
            $choices = array_merge($choices, $this->vacancyManager->provideChoices($vacancies));
     
            $form->add('offer', NoLabelChoiceType::Class, array(
                'choices' => $choices,
                'label' => false
            ));
            
        });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\GroupBundle\Entity\Application',
            'translation_domain' => 'form',
            'image_attr' => array('src' => null, 'alt' => null)
        ));
        
        $resolver->setAllowedTypes('image_attr', 'array');
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options) {
        //throw new \RuntimeException('options : ' . print_r($options['attr'], true));
        $tab = array('image_attr' => $options['image_attr']);
        $view->vars = array_merge($view->vars, $tab);
    }
    
    public function getBlockPrefix() {
        return 'lef_offer';
    }
    
}
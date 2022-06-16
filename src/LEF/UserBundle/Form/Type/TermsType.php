<?php
namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use FOS\UserBundle\Form\Type\UsernameFormType as FOSUsernameFormType;

class TermsType extends AbstractType { 
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'form'
        ));
    }
    
    public function getBlockPrefix() {
        return 'lef_user_terms';
    }
    
    public function getParent() {
        return CheckboxType::class;
    }
}
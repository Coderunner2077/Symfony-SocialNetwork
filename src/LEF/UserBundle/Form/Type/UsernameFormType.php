<?php
namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use FOS\UserBundle\Form\Type\UsernameFormType as FOSUsernameFormType;

class UsernameFormType extends AbstractType { 
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'FOSUserBundle'
        ));
    }
    
    public function getBlockPrefix() {
        return 'lef_user_username';
    }
    
    public function getParent() {
        return TextType::class;
    }
}
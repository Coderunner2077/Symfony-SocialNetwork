<?php
namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use LEF\CoreBundle\Form\Type\ImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvatarType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('avatar', ImageType::class, array('label' => 'form.avatar'))
                ->add('save', SubmitType::class, array('label' => 'form.upload'));
    }
 
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'LEF\UserBundle\Entity\User',
            'translation_domain' => 'form'
        ));
    }
    
}
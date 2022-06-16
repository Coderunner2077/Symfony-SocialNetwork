<?php
namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use LEF\CoreBundle\Form\Type\ImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\ProfileFormType as FOSProfileFormType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fullname', TextType::class, array('label' => 'form.fullname',
                    'translation_domain' => 'form'
                ))
                ->remove('username')
                ->add('username', UsernameFormType::class, array(
                    'label' => 'form.username',
                    'attr' => array('maxlength' => 50),
                    'constraints' => array(
                        new NotBlank(array('message' => "user.notblank.pseudo", 'groups' => ["Registration", "Profile"])),
                        new Length(array('min' => 4, 'max' => 50, 'minMessage' => "user.length.pseudo_min",
                            'maxMessage' => "user.length.pseudo_max", 'groups' => ["Registration", "Profile"])),
                        new Regex(array('pattern' => "/^\w+$/i", 'message' => "user.regex.pseudo", 'groups' => ["Registration", "Profile"])),
                        new Regex(array('pattern' => "/__/", 'match' => false, 'message' => "user.regex.pseudo_underscore",
                            'groups' =>["Registration", "Profile"]))
                    )
                ));
    }
 
    public function getParent() {
        return FOSProfileFormType::class;
    }
    
    public function getBlockPrefix()
    {
        return 'lef_user_profile';
    }
}
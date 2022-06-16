<?php
namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use FOS\UserBundle\Form\Type\RegistrationFormType as FOSRegistrationFormType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use LEF\UserBundle\Form\Type\UsernameFormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegistrationFormType extends AbstractType {
    protected $translator;
    
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fullname', TextType::class, array('label' => 'form.fullname',
                    'translation_domain' => 'form'
                ))
                ->remove('username')
                ->remove('plainPassword')
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'options' => array(
                        'translation_domain' => 'FOSUserBundle',
                        'attr' => array(
                            'autocomplete' => 'new-password',
                        ),
                    ),
                    'first_options' => array('label' => 'form.password',
                        'attr' => array(
                            'data-toggle' => 'tooltip',
                            'title' => $this->translator->trans('form.password.tooltip', array(), 'form'),
                            'data-placement' => 'auto',
                            'maxlength' => 35
                        ),
                        'constraints' => array(
                            new Length(array('min' => 8, 'max' => 35)),
                            new Regex(array('pattern' => '/[0-9]/', 'message' => 'password.no_number')),
                            new Regex(array('pattern' => '/[^a-zA-Z0-9]/', 'message' => 'password.no_special')),
                            new Regex(array('pattern' => '/[a-z]/i', 'message' => 'password.no_letter')),
                            new Regex(array('pattern' => '/\s|\t|\n/', 'match' => false, 'message' => 'password.space'))
                        )
                    ),
                    'second_options' => array('label' => 'form.password_confirmation'),
                    'invalid_message' => 'fos_user.password.mismatch',
                ))
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
                ))
                ->add('terms', TermsType::class, array(
                    'label' => 'form.terms', 'mapped' => false, 'required' => false,
                    'constraints' => array(
                        new NotBlank(array('message' => "user.notblank.terms", 'groups' => ['Registration', 'Profile']))
                    )
                ));
    }
 
    public function getParent() {
        return FOSRegistrationFormType::class;
    }
    
    public function getBlockPrefix()
    {
        return 'lef_user_registration';
    }
}
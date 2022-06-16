<?php
namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use LEF\CoreBundle\Form\Type\ImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as FOSChangePasswordFormType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordFormType extends AbstractType {
    private $class;
    protected $translator;
    
    public function __construct($class, TranslatorInterface $translator) {
        $this->class = $class;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->remove('plainPassword')
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
                            'data-placement' => 'auto'
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
                ));
    }
 
    public function getParent() {
        return FOSChangePasswordFormType::class;
    }
    
    public function getBlockPrefix()
    {
        return 'lef_user_change_password';
    }
}
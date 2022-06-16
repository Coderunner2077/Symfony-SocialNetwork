<?php
// src/LEF/UserBundle/Form/Type/AuthenticationFormType.php

namespace LEF\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class AuthenticationFormType extends AbstractType {
    protected $requestStack,
              $tokenManager;
    
    public function __construct (RequestStack $requestStack, CsrfTokenManagerInterface $tokenManager) {
        $this->requestStack = $requestStack;
        $this->tokenManager = $tokenManager;
    }
    
    public function buildForm (FormBuilderInterface $builder, array $options) {
        $builder->add('_password', PasswordType::class, array('label' => 'security.login.password'))
                ->add('_remember_me', CheckboxType::class, array(
                    'label' => 'security.login.remember_me',
                    'required' => false
                ))
                ->add('_submit', SubmitType::class, array('label' => 'security.login.submit'))
                ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $request = $this->requestStack->getCurrentRequest();
            $session = $request->getSession();
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
            
            // get the error if any (works with forward and redirect)
            if ($request->attributes->has($authErrorKey)) {
                $error = $request->attributes->get($authErrorKey);
            } elseif (null !== $session && $session->has($authErrorKey)) {
                $error = $session->get($authErrorKey);
            } else {
                $error = null;
            }
            
            if (!$error instanceof AuthenticationException)
                $error = null; // The value does not come from the security component
            
            // last username entered by the user
            $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);
            $csrfToken = $this->tokenManager 
                ? $this->tokenManager->getToken('authenticate')->getValue()
                : null;
            
            $form = $event->getForm();
            $form->add('_username', TextType::class, array(
                    'label' => 'security.login.username',
                    'data' => $lastUsername,
                    'attr' => array('placeholder' => 'security.login.username_email')
                 ));

            if($csrfToken) {
                $form->add('_csrf_token', HiddenType::class, array(
                    'data' => $csrfToken
                ));
            }
        });
    }
    
    public function configureOptions (OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'FOSUserBundle'
        ));
    }
    
    public function getBlockPrefix() {
        return null;
    }
}
<?php
// src/LEF/GroupBundle/Form/Type/RootInvitationType.php

namespace LEF\GroupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LEF\GroupBundle\Component\Vacancy\VacancyManager; 
use Doctrine\ORM\EntityManagerInterface;
use LEF\GroupBundle\Entity\Application;
use LEF\GroupBundle\Session\GroupBlockSession;
use LEF\GroupBundle\Security\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\LogicException;

class RootInvitationType extends AbstractType {
    protected $authContext;
    protected $vacancyManager; 
    protected $em;
    protected $session;
    
    public function __construct(AuthenticationContext $authContext, VacancyManager $vacancyManager, 
            EntityManagerInterface $em, GroupBlockSession $session, AuthorizationChecker $authChecker) {
        $this->authContext = $authContext;
        $this->vacancyManager = $vacancyManager;
        $this->em = $em; 
        $this->session = $session;
        $this->authChecker = $authChecker;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->authContext->getCurrentUser();
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $rep = $this->em->getRepository('LEFGroupBundle:MemberPrivilege');
            $applicant = $event->getData();
            $user = $this->authContext->getCurrentUser();
            $notGroupIds = $rep->getGroupIds($applicant->getId(), PrivilegeBitmasks::MEMBER);
            //throw new \RuntimeException('vila : ' . print_r($notGroupIds, true));
            $prvs = $rep->findWithGroup($user->getId(), PrivilegeBitmasks::HIRE, $notGroupIds, true);
            $groups = array_map(function($privilege) {
                return $privilege->getGroup();
            }, $prvs);
            $groupIds = array_map(function($group) { return $group->getId(); }, $groups);
            $form= $event->getForm();
            $rep2 = $this->em->getRepository('LEFGroupBundle:Application');
            $applications = $rep2->findWithGroup($applicant->getId(), $groups);
            $appGroupIds = array_map(function($application) {
                return $application->getGroup()->getId();
            }, $applications);
            
            foreach($groups as $group) {
                $isBlocked = $this->session->isBlocked($group->getId(), $applicant->getId());
                if(array_search($group->getId(), $appGroupIds) === false && $isBlocked === false)
                    $applications[] = new Application(['applicant' => $applicant, 'group' => $group]);
            }
            $declined = false; $member = false;
            foreach($applications as $key => $application) {
                if($application->isDeclined() && $application->isOffer()) {
                    $key = array_search($application->getGroup()->getId(), $groupIds);
                    unset($groupIds[$key]);
                    unset($applications[$key]);
                    $declined = true;
                    continue;
                }
                $group = $application->getGroup();
                $vacancies = $group->getVacancies();
                $application->setVacancies($vacancies);
                $form->add('invitation_' . $group->getId(), OfferType::class, array(
                        'data' => $application,
                        'mapped' => false,
                        'label' => $group->getName(),
                        'image_attr' => array(
                            'src' => $group->getLogo() ? $group->getLogo()->getSrc() : null,
                            'alt' => 'logo'
                        )
                    ));
            }
            if(count($applications) > 0) 
                $form->add('groups', HiddenType::class, array('data' => serialize($groupIds), 'mapped' => false))
                     ->add('submit', SubmitType::class, array(
                    'label' => 'form.invite', 'translation_domain' => 'form'));
            else {
                foreach($notGroupIds as $id) {
                    if($this->authChecker->hasPrivilege(PrivilegeBitmasks::HIRE, $id)) {
                        $member = true;
                        break;
                    }
                }
                if($declined && $member === false)
                    $form->add('declined_all', HiddenType::class, array('mapped' => false));
                elseif($declined)
                    $form->add('declined', HiddenType::class, array('mapped' => false));
                elseif($declined === false && $member === true)
                    $form->add('is_member', HiddenType::class, array('mapped' => false));
                elseif($declined == false && $member == false)
                    throw new LogicException('exception.logic.invitation');
            }                
        });
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array (
            'data_class' => 'LEF\UserBundle\Entity\User'
        ));
    }    
}
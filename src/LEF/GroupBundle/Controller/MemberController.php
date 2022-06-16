<?php
// src/LEF/GroupBundle/Controller/MemberController.php

namespace LEF\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Entity\MemberPrivilege;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\LogicException;
use LEF\CoreBundle\Controller\ControllerTrait;
use LEF\GroupBundle\Entity\Application;
use LEF\GroupBundle\Form\Type\ApplicationType; 
use LEF\GroupBundle\Form\Type\InvitationType;
use LEF\GroupBundle\Form\Type\RootInvitationType;
use LEF\GroupBundle\Form\Type\GrantType; 
use Doctrine\Common\Collections\ArrayCollection;
use LEF\UserBundle\Entity\User;

class MemberController extends Controller {
    use ControllerTrait;
    public $pathPrefix = 'LEFGroupBundle\\Member\\';
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function applyAction(Group $group, Request $request) {
        if($this->get('vacancy_checker')->hasVacancy($group) === false)
            throw new LogicException('exception.logic.lefgroup.no_vacancy');
        $user = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:Application');
        $application = $rep->findOneBy(['applicant' => $user, 'group' => $group]);
        $canApply = true;
        if(empty($application))
            $application = new Application(['applicant' => $user, 'group' => $group]);
        elseif(!$application->canApply())
            $canApply = false;
            //throw new LogicException('exception.logic.lefgroup.apply');
            
        $application->setVacancies($group->getVacancies());
        $form = $this->get('form.factory')->create(ApplicationType::class, $application);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() && $canApply) {
            $translator = $this->get('bitmask_translator');
            $application->setDemand($translator->actionToRole($application->getDemand()));
            $application->processDemand();
            $application->setRequestedAt(new \DateTime());
            $em->persist($application);
            $id = $application->getId();
            $em->flush();
            $session = $this->get('invitation_session');
            if($session->has('invitation', $id))
                $session->remove('invitation', $id);
            $htmlId = 'application-'.$application->getId();
            $status = $translator->translateStatus($application->getStatus());
            $demand = $translator->translateMask($application->getDemand());
            $link = '<a href="' . $this->generateUrl('lef_group_cancel', array('id' => $application->getId()));
            $link .= '" class="group-action form-caller mb-2">' . $this->get('translator')->trans('lefcore.cancel') . '</a>';
            $vars = array(
                'id' => $htmlId, 'alert' => true, 'alertStatus' => 'success', 
                'alertText' => 'flash.' . ($id ? 'modified.' : 'added.') . 'application',
                'target' => ['.form-demand', '#status-' . $application->getId(), '#links-' . $application->getId()],
                'text' => [$demand, $status, $link]
            );
            return $this->doRender($request, 'lef_group_view', array('id' => $group->getId()), $vars);
        }
        
        return $this->doRender($request, 'apply.html.twig', array('form' => $form->createView(), 'application' => $application,
            'group' => $group
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function inviteAction(Group $group, Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        if($this->get('group_authorization')->isGranted(PrivilegeBitmasks::HIRE, $group) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.invite');
        if($request->query->has('user') === false)
            throw new LogicException('exception.logic.routing');
        
        $rep = $em->getRepository('LEFGroupBundle:Application');
        $userId = $request->query->get('user');
        $application = $rep->findOneWithUserGroup($userId, $group->getId());
        $canInvite = true;
        if(empty($application)) {
            $user = $em->getRepository('LEFUserBundle:User')->findOne($userId);
            if(empty($user))
                throw new NotFoundHttpException('exception.notfound.lefgroup.invite');
            $application = new Application(['applicant' => $user, 'group' => $group]);
        } elseif(!$application->canInvite())
            $canInvite = false;
        $application->setVacancies($group->getVacancies());
                    
        $form = $this->get('form.factory')->create(InvitationType::class, $application);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() && $canInvite) {
            $translator = $this->get('bitmask_translator');
            $application->setOffer($translator->actionToRole($application->getOffer()));
            $application->processOffer(); 
            $id = $application->getId();
            
            $application->setRequestedAt(new \DateTime());
            $em->persist($application);
            $em->flush();
            $session = $this->get('invitation_session');
            if($session->has('application', $id))
                $session->remove('application', $id);
            $htmlId = 'application-'.$id;
            if($application->isDemand()) {
                $status = $translator->translateStatus($application->getStatus());
                $offer = $translator->translateMask($application->getOffer());
                $vars = array(
                    'id' => $htmlId,
                    'target' => ['.form-offer', '#status-' . $id],
                    'text' => [$offer, $status]
                );
            } 
            $vars['alert'] = true; $vars['alertStatus'] = 'success';
            $vars['alertText'] = 'flash.' . ($id ? 'modified.' : 'added.') . 'invitation';
                    
            return $this->doRender($request, 'lef_group_view', array('id' => $group->getId()), $vars);
        }
                
        return $this->doRender($request, 'invite.html.twig', array('form' => $form->createView(), 'invitation' => $application,
            'group' => $group
        ));
    }
   
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("user", options={"repository_method": "findOneWithAll"})
     */
    public function inviteUserAction(User $user, Request $request) {
        if($this->get('group_authorization')->isGranted(PrivilegeBitmasks::HIRE) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.invite');
        
        $form = $this->get('form.factory')->create(RootInvitationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $translator = $this->get('bitmask_translator');
            $groupIds = unserialize($form->get('groups')->getData());
            $invited = [];
            foreach($groupIds as $groupId) {
                $invitation = $form->get('invitation_' . $groupId)->getdata();
                if($invitation->getOffer() !== null) {
                    $invitation->setOffer($translator->actionToRole($invitation->getOffer()));
                    $invitation->processOffer();
                    $invId = $invitation->getId();
                    $invitation->setRequestedAt(new \DateTime());
                    $em->persist($invitation);
                    $invited[] = $invitation;
                } else 
                    $em->detach($invitation);
            }
            $em->flush();
            $session = $this->get('invitation_session');
            foreach($invited as $invitation) {
                if($session->has('application', $invitation->getId())) {
                    $session->remove('application', $invitation->getId());
                }
            }
               
            $vars = [];
            $hasInvited = count($invited) ? true : false;
            $vars['alert'] = true; $vars['alertStatus'] = $hasInvited ? 'success' : 'warning';
            $invite = count($invited) > 1 ? 'invitations' : 'invitation';
            $vars['alertText'] = 'flash.' . ($hasInvited ? 'added.' : 'not_added.') . $invite;
            if($hasInvited)
                return $this->doRender($request, 'lef_group_applications', array(), $vars);
            else 
                return $this->doRender($request, 'lef_group_invite_user', array('id' => $user->getId()), $vars);
        }
        return $this->doRender($request, 'inviteUser.html.twig', array('form' => $form->createView(), 'user' => $user));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("application", options={"repository_method": "findOneWithAll"})
     */
    public function declineAction(Application $application, Request $request) {
        $authChecker = $this->get('group_authorization');
        $isApplicant = $application->getApplicant() == $this->getUser();
        if(!$isApplicant && $authChecker->isGranted('HIRE', $application->getGroup()) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.decline');
            
        if(($isApplicant && !$application->isInvitation()) || (!$isApplicant && !$application->isApplication()))
            throw new LogicException('exception.logic.lefgroup.decline');
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $application->setStatus($isApplicant ? (Application::OFFER_DECLINED) : (Application::REFUSED));   
            $id = 'application-' . $application->getId();
            $em->flush();            
            $session = $this->get('invitation_session');
            $session->remove($isApplicant ? 'invitation' : 'application', $application->getId());
            $alertText = 'flash.declined.' . ($isApplicant  ? 'invitation' : 'application');
            return $this->doRender($request, 'lef_group_view', 
                array('id' => $application->getGroup()->getId()),
                array('id' => $id, 'alert' => true,
                      'alertStatus' => 'warning', 
                      'alertText' => $alertText
                )
            );
        }
        return $this->doRender($request, 'decline.html.twig', array(
            'form' => $form->createView(),
            'application' => $application,
            'isApplicant' => $isApplicant
        ));   
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("application", options={"repository_method": "findOneWithAll"})
     */
    public function cancelAction(Application $application, Request $request) {
        $authChecker = $this->get('group_authorization');
        $isApplicant = $application->getApplicant() == $this->getUser();
        if(!$isApplicant && $authChecker->isGranted('HIRE', $application->getGroup()) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.cancel');
            
        if(($isApplicant && !$application->isApplication()) || (!$isApplicant && !$application->isInvitation()))
            throw new LogicException('exception.logic.lefgroup.cancel');
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $translator = $this->get('bitmask_translator');
            $em = $this->get('doctrine.orm.entity_manager');
            $application->setStatus($isApplicant ? (Application::DEMAND_CANCELLED) : (Application::CANCELLED));
            $id = 'application-' . $application->getId();
            $em->flush();
            $alertText = 'flash.cancelled.' . ($isApplicant  ? 'invitation' : 'application');
            $url = $isApplicant ? $this->generateUrl('lef_group_apply', 
                array('id' => $application->getGroup()->getId(), 'user' => $this->getUser()->getId())) :
                $this->generateUrl('lef_group_invite', 
                    array('id' => $application->getGroup()->getId(), 'user' => $application->getApplicant()->getId()));
            $link = '<a href="' . $url;
            $cancel = $this->get('translator')->trans(($isApplicant ? 'application' : 'invitation') .'.manage');
            $link .= '" class="group-action form-caller mb-2">' . $cancel . '</a>';
            $status = $translator->translateStatus($application->getStatus());
            return $this->doRender($request, 'lef_group_view',
                array('id' => $application->getGroup()->getId()),
                array('id' => $id, 'alert' => true,
                      'alertStatus' => 'warning', 'alertText' => $alertText,
                      'target' => ['#status-' . $application->getId(), '#links-' . $application->getId()],
                      'text' => [$status, $link]
                )
            );
        }
        return $this->doRender($request, 'cancel.html.twig', array(
            'form' => $form->createView(),
            'application' => $application,
            'isApplicant' => $isApplicant
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("application", options={"repository_method": "findOneWithAll"})
     */
    public function hireAction(Application $application, Request $request) {
        $authChecker = $this->get('group_authorization');
        if($authChecker->isGranted('HIRE', $application->getGroup()) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.hire');
        if($application->isApplication() === false)
            throw new LogicException('exception.logic.lefgroup.hire');  
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $privilege = $this->get('group_privilege_manager')
                              ->persist($application->getApplicant(), 
                                        $application->getDemand(), 
                                        $application->getGroup());
            $id = 'application-' . $application->getId();
            $em->remove($application);
            $em->flush();
            $session = $this->get('invitation_session');
            $session->remove('application', $applicatin->getId());            
              
            return $this->doRender($request, 'lef_group_view',
                array('id' => $application->getGroup()->getId()),
                array('id' => $id, 'alert' => true,
                    'alertStatus' => 'success', 'alertText' => 'flash.accepted.application')
                );
        }
        return $this->doRender($request, 'hire.html.twig', array(
            'form' => $form->createView(),
            'application' => $application
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("application", options={"repository_method": "findOneWithAll"})
     */
    public function joinAction(Application $application, Request $request) {
        if($application->getApplicant() != $this->getUser())
            throw new AccessDeniedException('exception.accessdenied.lefgroup.join');
        if($application->isInvitation() === false)
            throw new LogicException('exception.logic.lefgroup.join');
        $form = $this->get('form.factory')->create();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $privilege = $this->get('group_privilege_manager') 
                        ->persist($application->getApplicant(),
                        $application->getOffer(),
                        $application->getGroup());
            $id = 'application-' . $application->getId();
            $em->remove($application);
            $em->flush();
            $session = $this->get('invitation_session');
            $session->remove('invitation', $application->getId());
                    
            return $this->doRender($request, 'lef_group_view',
                array('id' => $application->getGroup()->getId()),
                array('id' => $id, 'alert' => true,
                    'alertStatus' => 'success', 'alertText' => 'flash.accepted.invitation')
                );
            }
        return $this->doRender($request, 'join.html.twig', array(
            'form' => $form->createView(),
            'application' => $application
        ));
    }
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function fireAction(Group $group, Request $request) {
        $authChecker = $this->get('group_authorization');
        if($authChecker->isGranted('FIRE', $group) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.fire');
        $em = $this->get('doctrine.orm.entity_manager');
        if(!$request->query->has('member') || !is_numeric($request->query->get('member')))
            throw new LogicException('exception.logic.routing');
        $privilege = $em->getRepository('LEFGroupBundle:MemberPrivilege')
                        ->findOneWithAll($request->query->get('member'), $group->getId());
                        
        if(empty($privilege))
            throw new NotFoundHttpException('exception.notfound.lefgroup.fire');
        $allowed = true;
        if(!$authChecker->isJunior($privilege))
            $allowed = false;
        $form = $this->get('form.factory')->create();
        if($allowed && $request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $id = 'member-' . $privilege->getMember()->getId();
            $em->remove($privilege);
            $em->flush();        
            return $this->doRender($request, 'lef_group_view',
                array('id' => $group->getId()),
                array('id' => $id, 'alert' => true,
                      'alertStatus' => 'warning', 'alertText' => 'flash.fired.member')
            );
        }
        return $this->doRender($request, 'fire.html.twig', array(
            'form' => $form->createView(),
            'privilege' => $privilege,
            'allowed' => $allowed
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function grantAction(Group $group, Request $request) {
        $authChecker = $this->get('group_authorization');
        if($authChecker->isGranted('GRANT', $group) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.grant');
        $em = $this->get('doctrine.orm.entity_manager');
        if(!$request->query->has('member') || !is_numeric($request->query->get('member')))
            throw new LogicException('exception.logic.routing');
        $vars['privilege'] = $privilege
                           = $em->getRepository('LEFGroupBundle:MemberPrivilege')
                                ->findOneWithAll($request->query->get('member'), $group->getId());
        if(empty($privilege))
            throw new NotFoundHttpException('exception.notfound.lefgroup.grant');
        $vars['allowed'] = $allowed = true;
        if(!$authChecker->isJunior($privilege))
            $vars['allowed'] = $allowed = false;
        else {
            $form = $this->get('form.factory')->create(GrantType::class, $privilege);  
            $form->handleRequest($request);
        }
        if($allowed && $form->isSubmitted() && $form->isValid()) {
            $id = 'member-' . $privilege->getMember()->getId();
            $role = $this->get('bitmask_translator')->actionToRole($form->get('role')->getData());
            $privilege->setMasks($role);
            $em->flush();
            $role = $this->get('bitmask_translator')->translateMask($role);
            return $this->doRender($request, 'lef_group_view',
                array('id' => $group->getId()),
                array('id' => $id, 'alert' => true, 
                    'alertStatus' => 'success', 'alertText' => 'flash.granted.role',
                    'target' => ['.member'], 'text' => [$role]
                )
            );
        }
        if($allowed)
            $vars['form'] = $form->createView();
        return $this->doRender($request, 'grant.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function quitAction(Group $group, Request $request) {
        $authChecker = $this->get('group_authorization');
        if($authChecker->isGranted('MEMBER', $group) !== true)
            throw new LogicException('exception.logic.lefgroup.quit');
        $em = $this->get('doctrine.orm.entity_manager');
        $quittable = true;
        $privilege = $authChecker->getPrivilege();
        if(empty($privilege))
            throw new LogicException('exception.logic.lefgroup.quit');
        if($authChecker->isGranted('ADMIN', $group)) {
            $privileges = $em->getRepository('LEFGroupBundle:MemberPrivilege')
                             ->findMembersUpper($group->getId(), PrivilegeBitmasks::ADMIN);
            
            if(count($privileges) < 2)
                $quittable = false;
        }
        if($quittable) {
            $form = $this->get('form.factory')->create();
            $form->handleRequest($request);
        }
        if(!empty($form) && $form->isSubmitted() && $form->isValid()) {
            $id = 'group-' . $group->getId();
            $em->remove($privilege);
            $em->flush();
            $this->get('group_session')->removePrivilege($group->getId());
            return $this->doRender($request, 'lef_group_home', array(),
                array('id' => $id, 'alert' => true,
                      'alertStatus' => 'warning', 'alertText' => 'flash.quitted',
                )
            );
        }
        if($quittable)
            $vars['form'] = $form->createView();
        $vars['quittable'] = $quittable;
        $vars['group'] = $group;
        return $this->doRender($request, 'quit.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function userApplicationsAction(Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:Group');
        
        $applications = $em->getRepository('LEFGroupBundle:Application')->findWithAllBy(
            array('applicant' => $this->getUser()->getId()), $withRefused = true
        );
        $apps = new ArrayCollection($applications);
        $invitations = [];
        foreach($apps as &$app) {
            if($app->isInvitation()) {
                $apps->removeElement($app);
                $invitations[] = $app;
            }
        }
        
        return $this->doRender($request, 'userApplications.html.twig', array(
            'applications' => $apps, 'user' => $this->getUser(), 'invitations' => $invitations
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function groupApplicationsAction(Request $request) {
        $authChecker = $this->get('group_authorization');
        if($authChecker->isGranted('HIRE') !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.group_applications');
        
        $groupSession = $this->get('group_session');
        $groups = $groupSession->getGroups(PrivilegeBitmasks::HIRE);
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:Group');
        
        $apps = $em->getRepository('LEFGroupBundle:Application')->findWithAllBy(
            array('groups' => $groups), $withRefused = true
        );
        $apps = new ArrayCollection($apps);
        
        $applications = [];
        $invitations = [];
        foreach($apps as &$app) {
            if($app->isApplication()) {
                $apps->removeElement($app);
                $applications[] = $app;
            } elseif($app->isInvitation()) {
                $apps->removeElement($app);
                $invitations[] = $app;
            }
        }
        
        return $this->doRender($request, 'groupApplications.html.twig', array(
            'applications' => $apps, 'user' => $this->getUser(), 'awaiting_apps' => $applications,
            'invitations' => $invitations
        ));
    }
}
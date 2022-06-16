<?php
// src/LEF/GroupBundle/Controller/DefaultController.php

namespace LEF\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use LEF\GroupBundle\Form\Type\GroupType;
use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Entity\MemberPrivilege;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use LEF\CoreBundle\Controller\ControllerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use LEF\GroupBundle\Form\Type\VacancyType;
use LEF\GroupBundle\Entity\GroupEvent;
use LEF\GroupBundle\Form\Type\GroupEventType;

class DefaultController extends Controller {
    use ControllerTrait;
    public $pathPrefix = 'LEFGroupBundle\\Default\\';
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request) {        
        $group = new Group();
        $form = $this->get('form.factory')->create(GroupType::class, $group);
        
        
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->get('doctrine.orm.entity_manager');
            $group->addVacancies($form->get('vacancies')->getData());
            $em->persist($group);
            $privilege = $this->get('group_privilege_manager')->persist($user, PrivilegeBitmasks::ADMIN, $group);
            $em->flush();
            
            $request->getSession()->getFlashbag()->add('info', 'flash.created.group');
            $this->get('group_session')->updateSession($privilege);
            
            return $this->redirectToRoute('lef_group_view', array(
                'id' => $group->getId()
            ));
        }
        
        return $this->render('LEFGroupBundle\Default\create.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction(Group $group, Request $request) {
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::DICTATE, $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.edit');
        
        $form = $this->get('form.factory')->create(GroupType::class, $group);
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->get('doctrine.orm.entity_manager');
            $group->addVacancies($form->get('vacancies')->getData());
           
            $em->flush();

            $request->getSession()->getFlashbag()->add('info', 'flash.modified.group');
                
            return $this->redirectToRoute('lef_group_view', array(
                    'id' => $group->getId()
                ));
            }
            
        return $this->render('LEFGroupBundle\Default\edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Group $group, Request $request) {
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::DICTATE, $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.delete');
            
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->get('doctrine.orm.entity_manager');
            $id = $group->getId();
            $em->remove($group);
            $em->flush();
            
            $this->get('group_session')->removePrivilege($id);
                
            $request->getSession()->getFlashbag()->add('info', 'flash.deleted.group');
                
            return $this->redirectToRoute('lef_group_home');
        }
            
        return $this->render('LEFGroupBundle\Default\delete.html.twig', array(
            'form' => $form->createView(), 'group' => $group
        ));
    }
    
    
    
    /**
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function viewAction(Group $group) {
        if($this->get('group_block_session')->isBlocker($group->getId()))
            return $this->render('LEFGroupBundle\Default\blocked_view.html.twig', array('group' => $group));
            //throw new AccessDeniedException('exception.accessdenied.lefgroup.blocked');
        $em = $this->get('doctrine.orm.entity_manager');
        $id = (int) $group->getId();
        $rep = $em->getRepository('LEFGroupBundle:Group');
        $htmlTree = $rep->childrenHierarchy(
            $group, // if null: starting from root nodes
            true,  // true: load all children, false: only direct ones
            array(
                'decorate' => true,
                'representationField', 'name',
                'html' => true
            )
        );
        
        $followers = $em->getRepository('LEFUserBundle:User')->findGroupFollowers($group->getId());
        
        $memRep = $em->getRepository('LEFGroupBundle:MemberPrivilege');
        $members = $memRep->findMembersUpper($group->getId(), PrivilegeBitmasks::MEMBER);
        $subscribers = $memRep->findMembersLower($group->getId(), PrivilegeBitmasks::SUBSCRIBER);
        $id = $group->getGroupCategory()->getId();
        $vars = array(
            'group' => $group,
            'htmlTree' => $htmlTree,
            'members' => $members,
            'subscribers' => $subscribers,
            'followers' => $followers
        );
        
        if($this->get('group_authorization')->isGranted('HIRE', $group)) {
            $applications = $em->getRepository('LEFGroupBundle:Application')->findWithAllBy(['group' => $group]);
            $vars['applications'] = $applications;
        } else 
            $vars['nb_applications'] = $em->getRepository('LEFGroupBundle:Application')->count(['group' => $group]);
        if($group->isDemocratic()) {
            $groupEvent = $em->getRepository('LEFGroupBundle:GroupEvent')->findNextOne($group->getId());
            if(!empty($groupEvent))
                $vars['groupEvent'] = $groupEvent;
        }
        return $this->render('LEFGroupBundle\Default\view.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:Group');
        $privileges = $this->get('group_session')->getPrivileges();
        
        $groups = $this->get('group_session')->getGroups();
        $groups = $rep->findGroups($groups);
        $ids = array_map(function($group) {
            return $group->getId();
        }, $groups);
        $userRep = $em->getRepository('LEFUserBundle:User');
        $followed = $userRep->findOneWithFollowedGroups($this->get('authentication_context')->getUser()->getId(), $ids)
                            ->getFollowedGroups();
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
        return $this->render('LEFGroupBundle\Default\index.html.twig', array(
            'privileges' => $privileges, 'followed' => $followed, 'groups' => $groups,
            'applications' => $apps, 'invitations' => $invitations
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function followAction(Group $group, Request $request) {
        $groupSession = $this->get('group_session');
        if($groupSession->isFollowed($group->getId()) == true)
            throw new LogicException('exception.logic.lefgroup.follow');
        $em = $this->get('doctrine.orm.entity_manager');
        $isFollowed = $em->getRepository('LEFUserBundle:User')
                   ->isGroupFollowed($this->getUser()->getId(), $group->getId());
        if($isFollowed)
            throw new LogicException('exception.logic.lefgroup.follow');
        $user = $this->getUser();
        $em->persist($user);
        $user->addFollowedGroup($group);
        $em->flush();
        $groupSession->addFollowed($group->getId());
        return $this->doRender($request, 'lef_group_view', array('id' => $group->getId()),
            array('alert' => true, 'alertStatus' => 'success', 
                'alertText' => 'flash.followed.group', 
                'url' => $this->generateUrl('lef_group_unfollow', array('id' => $group->getId())),
                'text' => $this->get('translator')->trans('lefcore.unfollow'),
                'nbFollowers' =>  $this->get('number_shower')->showNumber($group->getNbFollowers()),
                'dataFollow' => 'group-' . $group->getId()
            )
        );
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function unfollowAction(Group $group, Request $request) {
        $groupSession = $this->get('group_session');
        $id = $group->getId();
        if($groupSession->isFollowed($id) == false)
            throw new LogicException('exception.logic.lefgroup.unfollow');
        $em = $this->get('doctrine.orm.entity_manager');
        $isFollowed = $em->getRepository('LEFUserBundle:User')
                         ->isGroupFollowed($this->getUser()->getId(), $group->getId());
        if(!$isFollowed)
            throw new LogicException('exception.logic.lefgroup.unfollow');
        $user = $this->getUser(); $user->removeFollowedGroup($group);
        $em->persist($user);
        $em->flush();
        $groupSession->removeFollowed($id);
        return $this->doRender($request, 'lef_group_view', array('id' => $group->getId()),
            array('alert' => true, 'alertStatus' => 'info',
                  'alertText' => 'flash.unfollowed.group',
                  'url' => $this->generateUrl('lef_group_follow', array('id' => $id)),
                  'text' => $this->get('translator')->trans('lefcore.follow'),
                  'nbFollowers' => $this->get('number_shower')->showNumber($group->getNbFollowers()),
                  'dataFollow' => 'group-' . $id
            )
        );
    }
    
    /**
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function groupArticlesAction(Group $group, Request $request) {
        if($this->get('group_block_session')->isBlocker($group->getId()))
            return $this->render('LEFGroupBundle\Default\blocked_view.html.twig', array('group' => $group));
        //throw new AccessDeniedException('exception.accessdenied.lefgroup.blocked');
        $nombreNews = $this->container->getParameter('articles_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFArticleBundle:Article');
        $index = $request->query->get('index') ?: 0;
        $offset = $index * $nombreNews;
        $listeNews = $rep->findByGroupWithAll($group->getId(), array('publishedAt' => 'DESC'),
            $nombreNews, $offset);
        
        $scrollable = count($listeNews) ? 'true' : 'false';
        return $this->doRender($request, 'groupArticles.html.twig', array(
            'scrollable' => $scrollable,
            'articles' => $listeNews,
            'total' => count($listeNews),
            'index' => $index,
            'group' => $group
        ));
    }
    
    /**
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function unpublishedArticlesAction(Group $group, Request $request) {
        if(!$this->get('group_authorization')->isGranted('EDIT', $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.unpublished_articles');
        $nombreNews = $this->container->getParameter('articles_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFArticleBundle:Article');
        $index = $request->query->get('index') ?: 0;
        $offset = $index * $nombreNews;
        $listeNews = $rep->findByGroupWithAll($group->getId(), array('publishedAt' => 'DESC'),
            $nombreNews, $offset, $published = false);
        
        $scrollable = count($listeNews) ? 'true' : 'false';
        return $this->doRender($request, 'unpublishedArticles.html.twig', array(
            'scrollable' => $scrollable,
            'articles' => $listeNews,
            'total' => count($listeNews),
            'index' => $index,
            'group' => $group
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function subscribeAction(Group $group, Request $request) {
        return $this->doRender($request, 'subscribe.html.twig', array());
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function updateVacanciesAction(Group $group, Request $request) {
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::HIRE, $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.update_vacancies');
            
        $form = $this->get('form.factory')->create(VacancyType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->get('doctrine.orm.entity_manager');
            $group->addVacancies($form->get('vacancies')->getData());
            
            $em->flush();
                
            $request->getSession()->getFlashbag()->add('info', 'flash.modified.vacancies');
                
            return $this->redirectToRoute('lef_group_view', array(
                'id' => $group->getId()
            ));
        }
            
        return $this->render('LEFGroupBundle\Default\updateVacancies.html.twig', array(
            'form' => $form->createView(), 'group' => $group
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function planElectionAction(Group $group, Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
      
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::DICTATE, $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.plan_election');
        if($group->isDemocratic())
            $groupEvent = $em->getRepository('LEFGroupBundle:GroupEvent')->findNextOne($group->getId());
        if(empty($groupEvent))
            $groupEvent = new GroupEvent(['group' => $group]);
        if(!$group->isDemocratic())
            return $this->doRender($request, 'planElection.html.twig', 
                array('groupEvent' => $groupEvent, 'group' => $group));
        $form = $this->get('form.factory')->create(GroupEventType::class, $groupEvent);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $id = $groupEvent->getId();
            if(empty($id))
                $em->persist($groupEvent);
            $em->flush();
            $flash = 'flash.' . (empty($id) ? 'added' : 'modified') . '.election';
            $request->getSession()->getFlashbag()->add('success', $flash);
            return $this->redirectToRoute('lef_group_view', array('id' => $group->getId()));
        }
        return $this->doRender($request, 'planElection.html.twig', array(
            'form' => $form->createView(), 'groupEvent' => $groupEvent, 'group' => $group
        ));        
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function blockAction(Group $group, Request $request) {
        $currentUser = $this->getUser();
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::BLOCK, $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.block');
        if(!$request->query->has('user')) 
            throw new LogicException('exception.logic.routing');
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('LEFUserBundle:User')->findOneWithAll((int)$request->query->get('user'));
        if(empty($user))
            throw new NotFoundHttpException('exception.notfound.lefgroup.block');
        if($user == $this->getUser())
            throw new LogicException('exception.logic.block');
        $session = $this->get('group_block_session');
        $privilege = $session->checkPrivilege($group->getId(), $user->getId());
        if(!empty($privilege) && $privilege->getMasks() > 0)
            return $this->doRender($request, 'block.html.twig', array(
                'group' => $group, 'privilege' => $privilege, 'isBlocked' => false));
        $isBlocked = !empty($privilege);
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if($isBlocked)
                $em->remove($privilege);
            else
                $privilege = $this->get('group_privilege_manager')->persist($user, PrivilegeBitmasks::BLOCKED, $group);
            
            $em->flush();
            if($isBlocked)
                $session->removeBlocked($group->getId(), $user->getId());
            else
                $session->addBlocked($group->getId(), $user->getId());
            $id = 'blocked-'  . $user->getId();
            $text = $this->get('translator')
                         ->trans($isBlocked ? 'lefgroup.block' : 'lefgroup.unblock');
            $html = '<i class="fas fa-' . ($isBlocked ? 'ban' : 'check-circle') .'"></i> '. $text;
            $class= 'form-caller blocker text-bold btn btn-' . ($isBlocked ? 'danger' : 'warning');
            return $this->doRender($request, 'lef_group_block', array('id' => $group->getId(),
                'user' => $user->getId()),
                array('id' => $id, 'alert' => true, 'alertStatus' => 'warning',
                      'alertText' => 'flash.'.($isBlocked ? 'un' : '') . 'blocked.group_user',
                      'target' => ['.blocker'], 'text' => [$html], 'classList' => [$class]
                )
            );
        }
        return $this->doRender($request, 'block.html.twig', array(
            'form' => $form->createView(),
            'group' => $group,
            'user' => $user,
            'isBlocked' => $isBlocked
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function viewBlockedAction(Request $request) {
        $session = $this->get('post_session');
        $em = $this->get('doctrine.orm.entity_manager');
        $blocked = $em->getRepository('LEFUserBundle:User')
        ->findUsers(array_values($session->getBlocked()));
        return $this->doRender($request, 'viewBlocked.html.twig',
            array('user' => $this->getUser(), 'blockedUsers' => $blocked)
            );
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function viewBlockersAction(Request $request) {
        $session = $this->get('post_session');
        $session->startPostBlockedSession();
        $em = $this->get('doctrine.orm.entity_manager');
        $blockers = $em->getRepository('LEFUserBundle:User')
        ->findUsers(array_values($session->getBlockers()));
        return $this->doRender($request, 'viewBlockers.html.twig',
            array('user' => $this->getUser(), 'blockers' => $blockers)
            );
    }
    
}
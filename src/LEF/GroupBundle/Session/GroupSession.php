<?php
// src/LEF/GroupBundle/Session/GroupSession.php

namespace LEF\GroupBundle\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\ORM\EntityManagerInterface;
use LEF\GroupBundle\Entity\MemberPrivilegeInterface;
use LEF\GroupBundle\Entity\MemberPrivilege as MemberPrivilegeEntity;
use LEF\GroupBundle\Entity\Group;

class GroupSession {
    protected $authContext;
    protected $session;
    protected $em;

    public function __construct(AuthenticationContext $authContext, Session $session, EntityManagerInterface $em) {
        $this->authContext = $authContext;
        $this->session = $session;
        $this->em = $em;
    }
    
    public function initialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        if($this->session->has('group_session'))
            return true;
        
        $this->startGroupSession();
        return true;
    }
    
    public function startGroupSession() {
        $repository = $this->em->getRepository('LEFGroupBundle:MemberPrivilege');
        $privileges = $repository->getCardinals($this->authContext->getUser()->getId());
        $newTab = [];
        foreach($privileges as $privilege) {
            $newTab['group_' . $privilege['id']] = new MemberPrivilege([
                'group' =>$privilege['id'], 'masks' => $privilege['masks'], 
                'member' => $this->authContext->getUser()->getId(),
                'updatedAt' => $privilege['updatedAt']
            ]);
        }
        $this->session->set('group_session', $newTab);
        
        $userRep = $this->em->getRepository('LEFUserBundle:User');
        $followed = $userRep->getFollowedGroupIds($this->authContext->getUser()->getId());
        $follTab = [];
        foreach($followed as $id) {
            $follTab['followed_' . $id] = $id;
        }
        $this->session->set('group_follower_session', $follTab);
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        $this->startGroupSession();
        return true;
    }
    
    public function hasSession($groupId = null) {
        if($this->session->has('group_session') === false)
            return false;
        
        return isset($this->session->get('group_session')['group_'.$groupId]);
    }
    
    public function updateSession($data) {
        if($data instanceof MemberPrivilegeInterface) {
            $this->addPrivilege($data);
            return;
        }
        $groupId = $data;
            
        $privilege = $this->em->getRepository('LEFGroupBundle:MemberPrivilege')->checkPrivilege($this->authContext->getUser()->getId(), $groupId);
        if($privilege instanceof MemberPrivilegeInterface)
            $this->addPrivilege($privilege);
        else if($this->hasSession($groupId))
            $this->removePrivilege($groupId);
        return $privilege;
    }
    
    public function addPrivilege(MemberPrivilegeInterface $privilege) {
        if($privilege instanceof MemberPrivilegeEntity) 
            $privilege = new MemberPrivilege([
                'group' => $privilege->getGroup()->getId(), 'masks' => $privilege->getMasks(),
                'member' => $privilege->getMember()->getId(),
                'updatedAt' => $privilege->getGroup()->getUpdatedAt()
            ]);
        $privileges = $this->session->get('group_session');
        $privileges['group_'.$privilege->getGroup()] = $privilege;
        $this->session->set('group_session', $privileges);
    }
    
    public function getPrivilege($groupId) {
        return $this->hasSession($groupId) ? $this->session->get('group_session')['group_'. $groupId] : null;
    }
    
    public function removePrivilege($groupId) {
        $session = $this->session->get('group_session');
        unset($session['group_'.$groupId]);
        $this->session->set('group_session', $session);
        //$this->session->remove('group_session/group_'. $groupId);
    }
    
    public function getFirstMasksPrivilege($mask) {
        $privileges = $this->session->get('group_session');
        if(empty($privileges))
            return false;
        foreach($privileges as $privilege) {
            if($privilege->isGranted($mask)) {
                $this->updateSession($privilege->getGroup());
                if($this->hasSession($privilege->getGroup()))
                    return $privilege;
            }
        }
        
        return false;
    }
    
    public function hasAlternative($mask, Group $group) {
        $privileges = $this->session->get('group_session');
        foreach($privileges as $privilege) {
            if($privilege->isGranted($mask) && $privilege->getGroup() != $group->getId()) 
                return $privilege;
        }
        
        return false;
    }
    
    public function getGroups($mask = null, Group $group = null) {
        $groups = [];
        $privileges = $this->session->get('group_session');
        if(empty($mask)) {
            if(empty($group))
                foreach($privileges as $privilege) {
                    $groups[] =  $privilege->getGroup();
                }
            else 
                foreach($privileges as $privilege)
                    if($group->getId() != $privilege->getGroup()) {
                        $groups[] =  $privilege->getGroup();
                    }
        } else {
            if(empty($group)) {
                foreach($privileges as $privilege)  {
                    if($privilege->isGranted($mask)) {
                        $groups[] =  $privilege->getGroup();
                    }
                }
            } else {
                foreach($privileges as $privilege)
                    if($privilege->isGranted($mask) && $group->getId() != $privilege->getGroup())
                        $groups[] = $privilege->getGroup();
            }    
        }
        
        return $groups;
    }
    
    public function getPrivileges($mask = null) {
        if(empty($mask))
            return $this->session->get('group_session');
        
        $total = $this->session->get('group_session');
        $privileges = [];
        foreach($total as $privilege) {
            if($privilege->isGranted($mask))
                $privileges[] = $privilege;
        }
        
        return $privileges;
    }
    
    public function destroySession() {
        $this->session->remove('group_session');
    }
    
    public function isFollowed($id) {
        if($this->session->has('group_follower_session') === false)
            return false;
            
        return isset($this->session->get('group_follower_session')['followed_'.$id]);
    }
    
    public function addFollowed($id) {
        $followed = $this->session->get('group_follower_session');
        $followed['followed_'.$id] = $id;
        $this->session->set('group_follower_session', $followed);
    }
    
    public function removeFollowed($id) {
        $followed = $this->session->get('group_follower_session');
        unset($followed['followed_'.$id]);;
        $this->session->set('group_follower_session', $followed);
    }
    
    public function getFollowers() {
        return $this->session->get('group_follower_session');
    }
    
    public function getUser() {
        return $this->authContext->getUser();
    }
}
<?php
// src/LEF/GroupBundle/Security/AuthorizationChecker.php

namespace LEF\GroupBundle\Security;

use LEF\GroupBundle\Session\GroupSession;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use LEF\GroupBundle\Entity\Group;
use Symfony\Component\Security\Core\User\UserInterface;
use LEF\GroupBundle\Entity\MemberPrivilege;
use LEF\GroupBundle\Bitmask\BitmaskTranslator;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;

class AuthorizationChecker {
    protected $authChecker;
    protected $groupSession;
    protected $bitmaskTranslator;
    protected $privilege;
   
    const NO_GROUP = 0;
    const NO_MASK = 1;
    const EXPIRED = 2;
    const GRANTED = 3;
    
    public function __construct(AuthorizationCheckerInterface $authChecker, GroupSession $session, BitmaskTranslator $translator) {
        $this->authChecker = $authChecker;
        $this->groupSession = $session;
        $this->bitmaskTranslator = $translator;
    }
    
    public function isGranted($mask, Group $group = null, UserInterface $user = null) {
        $this->privilege = null;
        if(!empty($user))
            return $this->hasPrivilege($mask, $group, $user);
        
        if($this->hasPrivilege($mask, $group) !== true)
            return false;
        if(!empty($group)) {
            $this->privilege = $this->groupSession->updateSession($group->getId());
            return $this->hasPrivilege($mask, $group);
        }
        return true;
    }
    
    public function hasPrivilege($mask, Group $group = null, UserInterface $user = null) { 
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
            
        if(!empty($user) && $this->groupSession->getUser() == $user)
            return true;            
        if(is_string($mask))
            $mask = $this->bitmaskTranslator->reverse($mask);
        if(empty($group))
            $result = $this->checkPrivilege($mask);
        else 
            $result = $this->checkPrivilege($mask, $group, $group->getUpdatedAt());
                
        if($result === self::GRANTED)
            return true;
                            
        if($this->authChecker->isGranted('ROLE_ADMIN'))
            return true;
                        
        return false;
    }
    
    public function isSubscriber(Group $group) {
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        
        $privilege = $this->groupSession->getPrivilege($group->getId());
        if(empty($privilege))
            return false;
        return $privilege->getMasks() === PrivilegeBitmasks::SUBSCRIBER;
    }
    
    public function isBlocked(Group $group) {
        $privilege = $this->groupSession->getPrivilege($group->getId());
        if(empty($privilege))
            return false;
        
        return $privilege->getMasks() === 0;
    }
    
    public function checkPrivilege($mask, $group = null, \DateTime $updatedAt = null) {
        if(empty($group))
            $privilege = $this->groupSession->getFirstMasksPrivilege($mask);
        else
            $privilege = $this->groupSession->getPrivilege($group instanceof Group ? $group->getId() : $group);
        if(empty($privilege))
            return self::NO_GROUP;
        if($updatedAt instanceof \DateTime && $privilege->getUpdatedAt() != $updatedAt) {
            $this->groupSession->updateSession($group instanceof Group ? $group->getId() : $group);
            if($privilege->getUpdatedAt() != $updatedAt)
                return self::EXPIRED;
        }
        
        if($privilege->isGranted($mask) === false)
            return self::NO_MASK;
            
        return self::GRANTED;
    }
    
    public function isJunior(MemberPrivilege $junior) {
        $privilege = $this->groupSession->getPrivilege($junior->getGroup()->getId());
        if(empty($privilege))
            return false;
        return $junior->getMasks() < $privilege->getMasks();
    }
    
    public function getPrivilege() { return $this->privilege; }
}
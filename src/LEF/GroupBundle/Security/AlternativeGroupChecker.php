<?php
// src/LEF/GroupBundle/Security/AuthorizationChecker.php

namespace LEF\GroupBundle\Security;

use LEF\GroupBundle\Session\GroupSession;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use LEF\GroupBundle\Entity\Group;
use Symfony\Component\Security\Core\User\UserInterface;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use LEF\GroupBundle\Bitmask\BitmaskTranslator;

class AlternativeGroupChecker {
    protected $authChecker;
    protected $groupSession;
    protected $bitmaskTranslator;
    
    const NO_GROUP = 0;
    const NO_MASK = 1;
    const EXPIRED = 2;
    const GRANTED = 3;
    
    public function __construct(AuthorizationCheckerInterface $authChecker, GroupSession $session, BitmaskTranslator $translator) {
        $this->authChecker = $authChecker;
        $this->groupSession = $session;
        $this->bitmaskTranslator = $translator;
    }
    
    public function hasAlternative($mask, Group $group) {
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        
        if(is_string($mask))
            $mask = $this->bitmaskTranslator->reverse($mask);  
        
        $result = $this->checkPrivilege($mask, $group, $group->getUpdatedAt());
        
        if($result === self::GRANTED)
            return true;
        
        return false;
    }
    
    public function checkPrivilege($mask, Group $group, \DateTime $updatedAt = null) {
        $privilege = $this->groupSession->hasAlternative($mask, $group);
        if(empty($privilege))
            return self::NO_GROUP;
        if($updatedAt instanceof \DateTime && $privilege->getUpdatedAt() != $updatedAt) {
            $this->groupSession->updateSession($group->getId());
            $privilege = $this->groupSession->hasAlternative($mask, $group);
            if(empty($privilege))
                return self::NO_GROUP;
            /*if($privilege->getGroup()->getUpdatedAt() != $updatedAt)
                return self::EXPIRED;*/
        }
        if($privilege->isGranted($mask) === false)
            return self::NO_MASK;
                
        return self::GRANTED;
    }
}
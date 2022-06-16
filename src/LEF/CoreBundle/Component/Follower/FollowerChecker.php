<?php
// src/LEF/CoreBundle/Component/Follower/FollowerChecker.php

namespace LEF\CoreBundle\Component\Follower;

use LEF\UserBundle\Session\UserSession;
use LEF\GroupBundle\Session\GroupSession;
use LEF\GroupBundle\Entity\Group;

class FollowerChecker {  
    protected $groupSession;
    protected $userSession;
    
    public function __construct(GroupSession $groupSession, UserSession $userSession) {
        $this->groupSession = $groupSession;
        $this->userSession = $userSession;
    }
    
    public function isFollowed($entity) {
        if($entity instanceof Group)
            return $this->groupSession->isFollowed($entity->getId());
        else 
            return $this->userSession->isFollowed($entity->getId());
    }
}
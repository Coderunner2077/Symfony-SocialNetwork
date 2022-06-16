<?php
// src/LEF/GroupBundle/Twig/GroupSessionExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Session\GroupSession;
use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Entity\MemberPrivilege;

class GroupSessionExtension extends \Twig_Extension {
    protected $groupSession;
    
    public function __construct(GroupSession $groupSession) {
        $this->groupSession = $groupSession;
    }
    
    public function getPrivilege(Group $group) {
        return $this->groupSession->getPrivilege($group->getId());
    }
    
    public function isSamePrivilege(MemberPrivilege $privilege) {
        $myPriv = $this->groupSession->getPrivilege($privilege->getGroup()->getId());
        if(empty($myPriv))
            return false;
        $userId = $myPriv->getMember();
        return $userId == $privilege->getMember()->getId();
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('get_privilege', [$this, 'getPrivilege']),
            new \Twig_SimpleFunction('is_same_privilege', [$this, 'isSamePrivilege'])
        );
    }
}
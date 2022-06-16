<?php
// src/LEF/GroupBundle/Twig/GroupBlockSessionExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Session\GroupBlockSession;
use LEF\GroupBundle\Entity\Group;
use Symfony\Component\Security\Core\User\UserInterface;

class GroupBlockSessionExtension extends \Twig_Extension {
    protected $groupBlockSession;
    
    public function __construct(GroupBlockSession $groupBlockSession) {
        $this->groupBlockSession = $groupBlockSession;
    }
    
    public function isBlocked(Group $group, UserInterface $user, $checkExpire = true) {
        return $this->groupBlockSession->isBlocked($group->getId(), $user->getId(), $checkExpire);
    }
    
    public function isBlocker(Group $group, $checkExpire = true) {
        return $this->groupBlockSession->isBlocker($group->getId(), $checkExpire);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('is_blocked_by_group', [$this, 'isBlocked']),
            new \Twig_SimpleFunction('is_group_blocker', [$this, 'isBlocker'])
        );
    }
}
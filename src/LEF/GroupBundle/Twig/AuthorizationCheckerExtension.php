<?php
// src/LEF/GroupBundle/Twig/AuthorizationCheckerExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Security\AuthorizationChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizationCheckerExtension extends \Twig_Extension {
    protected $authChecker;
    
    public function __construct(AuthorizationChecker $authChecker) {  
        $this->authChecker = $authChecker;
    }
     
    public function hasPrivilege($mask, Group $group = null, UserInterface $user = null) {
        return $this->authChecker->hasPrivilege($mask, $group, $user);
    }
    
    public function isSubscriber(Group $group) {
        return $this->authChecker->isSubscriber($group);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('has_privilege', [$this, 'hasPrivilege']),
            new \Twig_SimpleFunction('is_subscriber', [$this, 'isSubscriber'])
        );
    }
}
<?php
// src/LEF/CoreBundle/Twig/AuthorizationCheckerExtension.php

namespace LEF\CoreBundle\Twig;

use LEF\PostBundle\Entity\Post;
use LEF\CoreBundle\Security\AuthorizationChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizationCheckerExtension extends \Twig_Extension {
    protected $authChecker;
    
    public function __construct(AuthorizationChecker $authChecker) {  
        $this->authChecker = $authChecker;
    }
     
    public function isGranted($mask, Post $group = null, UserInterface $user = null) {
        return $this->authChecker->isGranted($mask, $group, $user);
    }
    
    public function isBlocked(UserInterface $user, $thorough = false) {
        return $this->authChecker->isBlocked($user, $thorough);
    }
    
    public function isBlocker(UserInterface $user, $checkExpire = true) {
        return $this->authChecker->isBlocker($user, $checkExpire);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('is_allowed', [$this, 'isGranted']),
            new \Twig_SimpleFunction('is_blocked', [$this, 'isBlocked']),
            new \Twig_SimpleFunction('is_blocker', [$this, 'isBlocker'])
        );
    }
}
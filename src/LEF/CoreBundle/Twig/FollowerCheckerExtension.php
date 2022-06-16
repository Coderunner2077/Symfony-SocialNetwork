<?php
// src/LEF/CoreBundle/Twig/FollowerCheckerExtension.php

namespace LEF\CoreBundle\Twig;

use LEF\CoreBundle\Component\Follower\FollowerChecker;

class FollowerCheckerExtension extends \Twig_Extension {
    protected $followerChecker;
    
    public function __construct(FollowerChecker $followerChecker) {
        $this->followerChecker = $followerChecker;
    }
    
    public function isFollowed($userId) {
        return $this->followerChecker->isFollowed($userId);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('is_followed', [$this, 'isFollowed'])
        );
    }
}
<?php
// src/LEF/CoreBundle/Twig/NotifSessionExtension.php

namespace LEF\CoreBundle\Twig;

use LEF\CoreBundle\Session\NotifSession;

class NotifSessionExtension extends \Twig_Extension {
    public function __construct(NotifSession $notifSession) {
        $this->notifSession = $notifSession;
    }
    
    
    public function countArticles() {
        return $this->notifSession->countArticles();
    }
    
    public function countGroupPosts() {
        return $this->notifSession->countGroupPosts();
    }
    
    public function countPosts() {
        return $this->notifSession->countPosts();
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('count_articles', [$this, 'countArticles']),
            new \Twig_SimpleFunction('count_group_posts', [$this, 'countGroupPosts']),
            new \Twig_SimpleFunction('count_posts', [$this, 'countPosts'])
        );
    }
}
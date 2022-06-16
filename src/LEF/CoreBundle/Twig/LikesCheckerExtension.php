<?php
// src/LEF/CoreBundle/Twig/LikesCheckerExtension.php

namespace LEF\CoreBundle\Twig;

use LEF\CoreBundle\EntityLiker\LikesChecker;
use LEF\CoreBundle\Entity\Entity;

class LikesCheckerExtension extends \Twig_Extension {
    protected $likesChecker;
    
    public function __construct(LikesChecker $likesChecker) {
        $this->likesChecker = $likesChecker;
    }
    
    public function isLiked(Entity $entity) {
        return $this->likesChecker->isLiked($entity);
    }
    
    public function isDisliked(Entity $entity) {
        return $this->likesChecker->isDisliked($entity);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('isLiked', [$this, 'isLiked']),
            new \Twig_SimpleFunction('isDisliked', [$this, 'isDisliked'])
        );
    }
    
    public function getName() {
        return 'LefCoreLikesCheckerExtension';
    }
}
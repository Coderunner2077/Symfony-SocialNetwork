<?php
// src/LEF/CoreBundle/EntityLiker/LikesChecker.php

namespace LEF\CoreBundle\EntityLiker;

use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\Common\Persistence\ObjectManager;
use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\LogicException;

class LikesChecker {
    protected $authContext;
    protected $likesSession;
    
    public function __construct(AuthenticationContext $authContext, LikesSession $likesSession) {
        $this->authContext = $authContext;
        $this->likesSession = $likesSession;
    }
    
    public function resolveBaseClass(Entity $entity) {
        $baseName = [];
        preg_match('#\\\\([a-z]+)$#i', $entity->getClass(), $baseName);
            
        if(empty($baseName[1]))
            throw new LogicException('exception.logic.lefcore.likeschecker');
        return $baseName[1];
    }
    
    public function getBaseClass() { return $this->baseClass; }
    
    public function isLiked($entity, $id = null) {
        if(!$this->authContext->isUser())
            return false;
        if($entity instanceof Entity) 
            return $this->likesSession->hasSession(lcfirst($this->resolveBaseClass($entity)) . 'Like', $entity->getId());
        
        return $this->likesSession->hasSession($entity . 'Like', $id);
    }
              
    public function isDisliked($entity, $id = null) {
        if(!$this->authContext->isUser())
            return false;
            
        if($entity instanceof Entity)
            return $this->likesSession->hasSession(lcfirst($this->resolveBaseClass($entity)) . 'Dislike', $entity->getId());
                
        return $this->likesSession->hasSession($entity . 'Dislike', $id);
    }
}
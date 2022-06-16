<?php
// src/LEF/CoreBundle/Security/AuthorizationChecker.php

namespace LEF\CoreBundle\Security;

use LEF\CoreBundle\Session\PostSession;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use LEF\PostBundle\Entity\Post;
use Symfony\Component\Security\Core\User\UserInterface;
use LEF\UserBundle\Entity\User;

class AuthorizationChecker {
    protected $authChecker;
    protected $postSession;
    
    public function __construct(AuthorizationCheckerInterface $authChecker, PostSession $session) {
        $this->authChecker = $authChecker;
        $this->postSession = $session;
    }
    
    public function isOwner(Post $post) {
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        $user = $this->postSession->getUser();
        if($post->getAuthor() == $user)
            return true;
        $lvl = $post->getLvl();
        $checkRoot = true;
        $isRepost = false;
        $reposter = null;
        switch($lvl) {
            case 0:
                $checkRoot = false;
            case 1:
                $isRepost = $post->isRepost();
                $reposter = $isRepost ? $post->getAuthor() : null;
                break;
            case 2:
                $isRepost = $post->getParent()->isRepost();
                $reposter = $isRepost ? $post->getParent()->getAuthor() : null;
                break;
            case 3:
                $isRepost = true;
                $reposter = $post->getParent()->getParent()->getAuthor();
                break;
        }
        if($reposter !== null)
            return $reposter == $user;
        elseif($checkRoot)
            return $post->getRoot()->getParent() == $user;
        
        if($this->authChecker->isGranted('ROLE_ADMIN'))
            return true;
        return false;           
    }
    
    public function canBan(Post $post) {
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        if($post->isRepost())
            return $post->getParent()->getAuthor() == $this->postSession->getUser();
        return false;
    }
    
    public function isGranted($action, Post $post = null, UserInterface $user = null) { 
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        if(empty($user))
            $user = $post->getAuthor();
        if($action == 'COMMENT' || $action == 'VIEW') {
            if($this->isBlocker($user))
                return false;
            return true;
        }            
        elseif($action == 'BAN')
            return $this->canBan($post);
        elseif($action == 'EDIT' || $action == 'DELETE')
            return $this->isOwner($post);
    }
    
    public function isBlocker(User $user, $checkExpire = true) {
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        $id = $user->getId();
        if($user == $this->postSession->getUser())
            return false;
        if($this->postSession->isBlocker($id, $checkExpire))
            return true;
     
        return false;
    }
    
    public function isBlocked(User $user, $thorough = false) {
        if(!$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return false;
        $id = $user->getId();
        if($user == $this->postSession->getUser())
            return false;
        if($this->postSession->isBlocked($id, $thorough))
            return true;
        //throw new \RuntimeException('voila : ' . print_r($this->postSession->getBlocked(), true));
        return false;
    }
}
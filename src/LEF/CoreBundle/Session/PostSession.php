<?php
// src/LEF/CoreBundle/Session/PostSession.php

namespace LEF\CoreBundle\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\ORM\EntityManagerInterface;

class PostSession {
    protected $authContext;
    protected $session;
    protected $em;

    public function __construct(AuthenticationContext $authContext, Session $session, EntityManagerInterface $em) {
        $this->authContext = $authContext;
        $this->session = $session;
        $this->em = $em;
    }
    
    public function initialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        if($this->session->has('post_blocker_session'))
            return true;
        
        $this->startPostSession();
        return true;
    }
    
    
    public function startPostBlockerSession() {
        $repository = $this->em->getRepository('LEFUserBundle:User');
        $blockers = $repository->getBlockersIds($this->authContext->getUser()->getId());
        $newTab = [];
        foreach($blockers as $blockerId) {
            $newTab['blocker_' . $blockerId] = $blockerId;
        }
        
        $this->session->set('post_blocker_session', $newTab);
        $this->session->set('post_blocker_session_expire', (time() + (10 * 60)));
    }
    
    public function startPostSession() {
        $this->startPostBlockerSession();
        $this->startPostBlockedSession();
    }
    
    public function startPostBlockedSession() {
        $repository = $this->em->getRepository('LEFUserBundle:User');
        $blockedIds = $repository->getBlockedIds($this->authContext->getUser()->getId());
        $bkdTab = [];
        foreach($blockedIds as $blocked)
            $bkdTab['blocked_'. $blocked] = $blocked;
        $this->session->set('post_blocked_session', $bkdTab);
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        $this->startPostBlockerSession();
        return true;
    }
    
    public function isExpired() {
        if($this->session->has('post_blocker_session_expire') == false)
            return true;
        $expire = $this->session->get('post_blocker_session_expire');
        if($expire < time())
            return true;
        return false;
    }
    
    public function isBlocker($blockerId, $checkExpire = true) {
        if($checkExpire === true && $this->isExpired())
            $this->reinitialize();
        
        return isset($this->session->get('post_blocker_session')['blocker_'.$blockerId]);
    }
    
    public function isBlocked($blockedId, $thorough = false) {
        if(!$thorough)
            return isset($this->session->get('post_blocked_session')['blocked_'.$blockedId]);
        if(!isset($this->session->get('post_blocked_session')['blocked_'.$blockedId]))
            return false;
        $isBlocked = $this->em->getRepository('LEFUserBundle:User')
                          ->isBlocked($this->authContext->getUser()->getId(), $blockedId);
        return $isBlocked;
    }
    
    public function updateBlockerSession($blockerId) {            
        $isBlocker = $this->em->getRepository('LEFUserBundle:User')->isBlocked($blockerId, $this->authContext->getUser()->getId());
        if($isBlocker)
            $this->session->set('post_blocker_session/blocker_'.$blockerId, $blockerId);
        else if($this->isBlocker($blockerId))
            $this->removeBlocker($blockerId);
        return $blockerId;
    }
    
    public function updateBlockedSession($blockedId) {
        $isBlocked = $this->em->getRepository('LEFUserBundle:User')->isBlocked($this->authContext->getUser()->getId(), $blockedId);
        if($isBlocked)
            $this->session->set('post_blocked_session/blocked_'.$blockedId, $blockedId);
        else if($this->isBlocked($blockedId))
            $this->removeBlocked($blockedId);
        return $blockedId;
    }
    
    public function destroySession() {
        $this->session->remove('post_blocker_session');
        $this->session->remove('post_blocker_session_expire');
        $this->session->remove('post_blocked_session');
    }
    
    public function addBlocker($blockerId) {
        $blockers = $this->session->get('post_blocked_session');
        $blockers['blocked_'.$blockerId] = (int)$blockerId;
        $this->session->set('post_blocker_session', $blockers);
    }
    
    public function removeBlocker($blockerId) {
        $session = $this->session->get('post_blocker_session');
        unset($session['blocker_' . $blockerId]);
        $this->session->set('post_blocker_session', $session);
    }
    
    public function addBlocked($blockedId) {
        $blocked = $this->session->get('post_blocked_session');
        $blocked['blocked_'.$blockedId] = (int)$blockedId;
        $this->session->set('post_blocked_session', $blocked);
    }
    
    public function removeBlocked($blockedId) {
        $session = $this->session->get('post_blocked_session');
        unset($session['blocked_' . $blockedId]);
        $this->session->set('post_blocked_session', $session);
    }
    
    public function getBlocked() {
        return $this->session->get('post_blocked_session');
    }
   
    public function getBlockers() {
        return $this->session->get('post_blocker_session');
    }
    
    public function getUser() {
        return $this->authContext->getUser();
    }
}
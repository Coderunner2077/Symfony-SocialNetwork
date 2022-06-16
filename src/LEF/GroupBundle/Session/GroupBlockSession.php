<?php
// src/LEF/GroupBundle/Session/PostSession.php

namespace LEF\GroupBundle\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\ORM\EntityManagerInterface;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;

class GroupBlockSession {
    protected $authContext;
    protected $session;
    protected $em;
    protected $groupSession;
    
    public function __construct(AuthenticationContext $authContext, Session $session, 
            EntityManagerInterface $em, GroupSession $groupSession) { 
        $this->authContext = $authContext;
        $this->session = $session;
        $this->em = $em;
        $this->groupSession = $groupSession;
    }
    
    public function initialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        if($this->session->has('group_blocker_session'))
            return true;
        
        $this->startGroupBlockSession();
        return true;
    }
    
    
    public function startGroupBlockerSession() {
        $repository = $this->em->getRepository('LEFGroupBundle:MemberPrivilege');
        $blockers = $repository->getBlockersIds($this->authContext->getUser()->getId());
        $newTab = [];
        foreach($blockers as $blockerId) {
            $newTab['blocker_' . $blockerId] = $blockerId;
        }
        
        $this->session->set('group_blocker_session', $newTab);
        $this->session->set('group_blocker_session_expire', (time() + (10 * 60)));
    }
    
    public function startGroupBlockSession() {
        $this->startGroupBlockerSession();
        $this->startGroupBlockedSession();
    }
    
    public function startGroupBlockedSession() {
        $repository = $this->em->getRepository('LEFGroupBundle:MemberPrivilege');
        $groupIds = $this->groupSession->getGroups(PrivilegeBitmasks::BLOCK);
        $blockedIds = $repository->getBlockedIds($groupIds);
        $bkdTab = [];
        foreach($blockedIds as $blocked)
            $bkdTab['blocked_'. $blocked['group_id'] .'_'. $blocked['user_id']] = $blocked['user_id'];
        $this->session->set('group_blocked_session', $bkdTab);
        $this->session->set('group_blocked_session_expire', (time() + (10 * 60)));
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        $this->startGroupBlockSession();
        return true;
    }
    
    public function isExpired($type = 'blocked') {
        if($this->session->has('group_' . $type . '_session_expire') == false)
            return true;
        $expire = $this->session->get('group_' . $type . '_session_expire');
        if($expire < time())
            return true;
        return false;
    }
    
    public function isBlocker($blockerId, $checkExpire = true) {
        if($checkExpire === true && $this->isExpired('blocker'))
            $this->reinitialize();
        
        return isset($this->session->get('group_blocker_session')['blocker_'.$blockerId]);
    }
    
    public function isBlocked($groupId, $userId, $checkExpire = true) {
        if($checkExpire === true && $this->isExpired('blocked'))
            $this->reinitialize();
        if(isset($this->session->get('group_blocked_session')['blocked_'.$groupId . '_'. $userId]))
            return true;
        
        return false;/*
        $isBlocked = $this->em->getRepository('LEFGroupBundle:MemberPrivilege')
                          ->isBlocked($groupId, $userId);
        return $isBlocked;*/
    }
    
    public function checkPrivilege($groupId, $userId) {
        return $this->em->getRepository('LEFGroupBundle:MemberPrivilege')
        ->checkPrivilege($userId, $groupId);
    }
    
    public function updateBlockerSession($blockerId) {            
        $isBlocker = $this->em->getRepository('LEFGroupBundle:MemberPrivilege')->isBlocked($blockerId, $this->authContext->getUser()->getId());
        if($isBlocker)
            $this->addBlocker($blockerId);
        else if($this->isBlocker($blockerId))
            $this->removeBlocker($blockerId);
        return $blockerId;
    }
    
    public function updateBlockedSession($groupId, $userId) {
        $isBlocked = $this->em->getRepository('LEFGroupBundle:MemberPrivilege')->isBlocked($groupId, $userId);
        if($isBlocked)
            $this->addBlocked($groupId, $userId);
        else if($this->isBlocked($blockedId))
            $this->removeBlocked($blockedId);
        return $blockedId;
    }
    
    public function destroySession() {
        $this->session->remove('group_blocker_session');
        $this->session->remove('group_blocker_session_expire');
        $this->session->remove('group_blocked_session');
    }
    
    public function addBlocker($blockerId) {
        $blockers = $this->session->get('group_blocked_session');
        $blockers['blocked_'.$blockerId] = (int)$blockerId;
        $this->session->set('group_blocker_session', $blockers);
    }
    
    public function removeBlocker($blockerId) {
        $session = $this->session->get('group_blocker_session');
        unset($session['blocker_' . $blockerId]);
        $this->session->set('group_blocker_session', $session);
    }
    
    public function addBlocked($groupId, $userId) {
        $blocked = $this->session->get('group_blocked_session');
        $blocked['blocked_'.$groupId . '_'. $userId] = (int)$userId;
        $this->session->set('group_blocked_session', $blocked);
    }
    
    public function removeBlocked($groupId, $userId) {
        $session = $this->session->get('group_blocked_session');
        unset($session['blocked_' . $groupId . '_' . $userId]);
        $this->session->set('group_blocked_session', $session);
    }
    
    public function getBlocked() {
        return $this->session->get('group_blocked_session');
    }
   
    public function getBlockers() {
        return $this->session->get('group_blocker_session');
    }
    
    public function getUser() {
        return $this->authContext->getUser();
    }
}
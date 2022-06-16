<?php
// src/LEF/GroupBundle/Session/InvitationSession.php

namespace LEF\GroupBundle\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\ORM\EntityManagerInterface;
use LEF\GroupBundle\Entity\MemberPrivilegeInterface;
use LEF\GroupBundle\Entity\MemberPrivilege as MemberPrivilegeEntity;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;

class InvitationSession {
    protected $authContext;
    protected $session;
    protected $em;
    protected $groupSession;

    const EXPIRE = 10 * 60;
    
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
        
        if($this->session->has('invitation_session'))
            return true;
        
        $this->startInvitationSession();
        return true;
    }
    
    public function startInvitationSession() {
        $repository = $this->em->getRepository('LEFGroupBundle:Application');
        $invitations = $repository->getIds($this->authContext->getUser()->getId());
        $newTab = [];
        foreach($invitations as $invitation) {
            $newTab['invitation_' . $invitation] = $invitation;
        }
        $this->session->set('invitation_session', $newTab);
        $this->session->set('invitation_session_expire', time() + self::EXPIRE);
        $groups = $this->groupSession->getGroups(PrivilegeBitmasks::HIRE);
        if(empty($groups))
            return;
        $applications = $repository->getAppIds($groups);
        $tab = [];
        foreach($applications as $application) {
            $tab['application_' . $application] = $application;
        }
        $this->session->set('application_session', $tab);
        $this->session->set('application_session_expire', time() + self::EXPIRE);        
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        $this->startInvitationSession();
        return true;
    }
    
    public function has($name, $id, $checkExpire = false) {
        if($checkExpire === true && $this->isExpired($name))
            $this->reinitialize();
            
        return isset($this->session->get($name . '_session')[$name . '_'.$id]);
    }
    
    public function countInvitations($checkExpire = true) {
        if($checkExpire === true && $this->isExpired('invitation'))
            $this->reinitialize();
        return count($this->session->get('invitation_session'));
    }
    
    public function countApplications($checkExpire = true) {
        if($checkExpire === true && $this->isExpired('application'))
            $this->reinitialize();
        return count($this->session->get('application_session'));
    }
    
    public function add($name, $id) {
        $ids = $this->session->get($name . '_session');
        $ids[$name . '_'.$id] = (int)$id;
        $this->session->set($name. '_session', $ids);
    }
    
    public function remove($name, $id) {
        $session = $this->session->get($name . '_session');
        unset($session[$name. '_' . $id]);
        $this->session->set($name. '_session', $session);
    }
    
    public function isExpired($name = null) {
        if(empty($name))
            $name = 'invitation';
        if($this->session->has($name. '_session_expire') == false)
            return false;
        $expire = $this->session->get($name . '_session_expire');
        if($expire < time())
            return true;
        return false;
    }
    
    public function get($name) {
        return $this->session->get($name . '_session');
    }
    
    public function setExpired($name, $time) {
        $this->session->set($name . '_session_expire', time() + $time);
    }
    
    public function getExpired($name) {
        return $this->session->get($name . '_session_expire');
    }
    
    public function destroySession() {
        $this->session->remove('invitation_session');
        $this->session->remove('application_session');
    }
}
<?php
// src/LEF/CoreBundle/EventSubscriber/SessionSubscriber.php

namespace LEF\CoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LEF\GroupBundle\Session\GroupSession;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use LEF\UserBundle\Session\UserSession;
use LEF\CoreBundle\Session\PostSession;
use LEF\CoreBundle\Session\NotifSession;
use LEF\GroupBundle\Session\InvitationSession; 
use LEF\GroupBundle\Session\GroupBlockSession;

class SessionSubscriber implements EventSubscriberInterface {
    protected $groupSession;
    protected $userSession;
    protected $postSession;
    protected $notifSession;
    protected $invitationSession;
    protected $groupBlockSession;
    
    public function __construct(GroupSession $groupSession, UserSession $userSession, 
            PostSession $postSession, NotifSession $notifSession, InvitationSession $invitationSession,
            GroupBlockSession $groupBlockSession) {
        $this->groupSession = $groupSession;
        $this->userSession = $userSession;
        $this->postSession = $postSession;
        $this->notifSession = $notifSession;
        $this->invitationSession = $invitationSession;
        $this->groupBlockSession = $groupBlockSession;
    }
    
    public function startSessions(InteractiveLoginEvent $event) {
        $this->groupSession->initialize();
        $this->userSession->initialize();
        $this->postSession->initialize();
        $this->notifSession->initialize();
        $this->invitationSession->initialize();
        $this->groupBlockSession->initialize();
    }
    
    public static function getSubscribedEvents() {
        return array(
            'security.interactive_login' => array('startSessions', -400)
        );
    }
}
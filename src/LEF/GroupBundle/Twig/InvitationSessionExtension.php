<?php
// src/LEF/GroupBundle/Twig/InvitationSessionExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Session\InvitationSession;

class InvitationSessionExtension extends \Twig_Extension {
    public function __construct(InvitationSession $invitationSession) {
        $this->invitationSession = $invitationSession;
    }
    
    public function countInvitations() {
        return $this->invitationSession->countInvitations();
    }
    
    public function countApplications() {
        return $this->invitationSession->countApplications();
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('count_invitations', [$this, 'countInvitations']),
            new \Twig_SimpleFunction('count_applications', [$this, 'countApplications'])
        );
    }
}
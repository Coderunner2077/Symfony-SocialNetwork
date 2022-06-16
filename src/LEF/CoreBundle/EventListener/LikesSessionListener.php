<?php
// src/LEF/CoreBundle/EventListener/LikesSessionListener.php

namespace LEF\CoreBundle\EventListener;

use LEF\CoreBundle\EntityLiker\LikesSession;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LikesSessionListener {
    protected $likesSession;
    
    public function __construct(LikesSession $likesSession) {
        $this->likesSession = $likesSession;
    }
    
    public function startLikesSession(InteractiveLoginEvent $event) {
        $this->likesSession->initialize();
    }
}
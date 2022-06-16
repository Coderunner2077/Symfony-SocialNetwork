<?php
// src/LEF/GroupBundle/EventListener/GroupPostEventListener.php

namespace LEF\GroupBundle\EventListener;

use LEF\CoreBundle\Notificator\Notificator;
use LEF\GroupBundle\Event\GroupPostEvent;

class GroupPostEventListener {
    protected $notificator;
    
    public function __construct(Notificator $notificator) {
        $this->notificator = $notificator;
    }
    
    public function onAddGroupPost(GroupPostEvent $event) {
        $this->notificator->addNotification($event->getPost());
    }
}
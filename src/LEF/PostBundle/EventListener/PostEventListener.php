<?php
// src/LEF/PostBundle/EventListener/PostEventListener.php

namespace LEF\PostBundle\EventListener;

use LEF\CoreBundle\Notificator\Notificator;
use LEF\PostBundle\Event\PostEvent;

class PostEventListener {
    protected $notificator;
    
    public function __construct(Notificator $notificator) {
        $this->notificator = $notificator;
    }
    
    public function onAddPost(PostEvent $event) {
        $this->notificator->addNotification($event->getPost());
    }
}
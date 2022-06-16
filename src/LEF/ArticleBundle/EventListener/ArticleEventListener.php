<?php
// src/LEF/ArticleBundle/EventListener/ArticleEventListener.php

namespace LEF\ArticleBundle\EventListener;

use LEF\CoreBundle\Notificator\Notificator;
use LEF\ArticleBundle\Event\ArticleEvent; 

class ArticleEventListener {
    protected $notificator;
    
    public function __construct(Notificator $notificator) {
        $this->notificator = $notificator;
    }
    
    public function onAddArticle(ArticleEvent $event) {
        $this->notificator->addNotification($event->getArticle());
    }
}
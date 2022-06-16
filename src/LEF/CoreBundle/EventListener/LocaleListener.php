<?php
// src/LEF/CoreBundle/EventListener/LocaleListener.php

namespace LEF\CoreBundle\EventListener;

use LEF\CoreBundle\EntityLiker\LikesSession;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleListener {
    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        if($request->cookies->has('_locale')) {
            $locale = $request->cookies->get('_locale');
            if($locale == 'en' || $locale == 'fr')
                $request->setLocale($locale);
        }
    }
       
}
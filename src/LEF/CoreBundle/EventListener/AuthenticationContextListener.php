<?php
// src/LEF/CoreBundle/EventListener/AuthenticationContextListener.php

namespace LEF\CoreBundle\EventListener;

use LEF\CoreBundle\Context\AuthenticationContext;

class AuthenticationContextListener {
    protected $authContext;
    
    public function __construct(AuthenticationContext $authContext) {
        $this->authContext = $authContext;
    }
    
    public function setUser() {
        $this->authContext->setUser();
    } 
}
<?php
// src/LEF/CoreBundle/Context/AuthenticationContext.php

namespace LEF\CoreBundle\Context;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationContext {
    protected $tokenStorage;
    protected $user;
    
    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }
    
    public function setUser() {
        $token = $this->tokenStorage->getToken();
        if($token !== null)
            $this->user = $token->getUser();
    }
    
    public function getCurrentUser() {
        return $this->isAuthenticated() ? $this->user : null;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function isUser() {
        return $this->user instanceof UserInterface;
    }
    
    public function isAuthenticated() {
        if($this->isUser())
            return true;
        
        $this->setUser();
        return $this->isUser();
    }
}
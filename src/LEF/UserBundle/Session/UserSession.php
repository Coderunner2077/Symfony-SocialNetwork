<?php
// src/LEF/UserBundle/Session/UserSession.php

namespace LEF\UserBundle\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\ORM\EntityManagerInterface;

class UserSession {
    protected $authContext;
    protected $session;
    protected $em;

    public function __construct(AuthenticationContext $authContext, Session $session, EntityManagerInterface $em) {
        $this->authContext = $authContext;
        $this->session = $session;
        $this->em = $em;
    }
    
    public function initialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        if($this->session->has('lef_user_session'))
            return true;
        
        $this->startUserSession();
        return true;
    }
    
    public function startUserSession() {
        $repository = $this->em->getRepository('LEFUserBundle:User');
        $followed = $repository->getFollowedIds($this->authContext->getUser()->getId());
        $newTab = [];
        foreach($followed as $id) {
            $newTab['followed_' . $id] = $id;
        }
        $this->session->set('lef_user_session', $newTab);
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        
        $this->startUserSession();
        return true;
    }
    
    public function hasSession($id = null) {
        if($this->session->has('lef_user_session') === false)
            return false;
        
        return isset($this->session->get('lef_user_session')['followed_'.$id]);
    }
    
    public function addFollowed($id) {
        $followed = $this->session->get('lef_user_session');
        $followed['followed_'.$id] = $id;
        $this->session->set('lef_user_session', $followed);
    }
    
    public function removeFollowed($id) {
        $followed = $this->session->get('lef_user_session');
        unset($followed['followed_'.$id]);;
        $this->session->set('lef_user_session', $followed);
    }
    
    public function getFollowers() {
        return $this->session->get('lef_user_session');
    }
    
    public function isFollowed($id) {
        return $this->hasSession($id);
    }
    
    public function destroySession() {
        $this->session->remove('lef_user_session');
    }
}
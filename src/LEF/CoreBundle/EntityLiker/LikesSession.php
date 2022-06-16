<?php
// src/LEF/CoreBundle/EntityLiker/LikesSession.php

namespace LEF\CoreBundle\EntityLiker;

use Symfony\Component\HttpFoundation\Session\Session;
use LEF\CoreBundle\Context\AuthenticationContext;
use Doctrine\ORM\EntityManagerInterface;

class LikesSession {
    protected $session;
    protected $authContext;
    protected $em;
    
    public function __construct(AuthenticationContext $authContext, Session $session, EntityManagerInterface $em) {
        $this->authContext = $authContext;
        $this->session = $session;
        $this->em = $em;
    }
    
    public function initialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
        if($this->session->has('articleLike'))
            return true;
        $this->startLikesSession();
        return true;
    }    
    
    public function startLikesSession() {
        $repository = $this->em->getRepository('LEFArticleBundle:ArticleLike');
        $likes = $repository->findLikes($this->authContext->getUser()->getId());
        
        $this->session->set('articleLike', $likes['article']);
        $this->session->set('commentLike', $likes['comment']);
        $this->session->set('groupPostLike', $likes['group_post']);
        $this->session->set('postLike', $likes['post']);
        //throw new \RuntimeException('voila : ' . print_r($likes['article'], true));
        
        $dislikes = $repository->findDislikes($this->authContext->getUser()->getId());
        
        $this->session->set('articleDislike', $dislikes['article']);
        $this->session->set('commentDislike', $dislikes['comment']);
        $this->session->set('groupPostDislike', $dislikes['group_post']);
        $this->session->set('postDislike', $dislikes['post']);   
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
            
        $this->startLikesSession();
        return true;
    }
    
    public function hasSession($name, $id = null) {
        if($this->session->has($name) === false)
            return false;
            
        return $this->session->get($name)->offsetExists($id);
    }
    
    public function add($name, $id) {
        $session = $this->get($name);
        $session[$id] = $id;
        $this->updateSession($name, $session);
        
        //$this->session->set($name . '/' . $id, $id);
    }
    
    public function remove($name, $id) {
        $session = $this->get($name);
        $session->remove($id);
        $this->updateSession($name, $session);
    }
    
    public function updateSession($name, $session) {        
        $this->session->set($name, $session);
    }
    
    public function getOne($name, $id) {
        return $this->hasSession($name, $id) ? $this->session->get($name)[$id] : null;
        
    }
    
    public function getLikes($name) {
        return $this->session->get($name . 'Like');
    }
    
    public function getDislikes($name) {
        return $this->session->get($name . 'Lislike');
    }
    
    public function get($name) {
        return $this->session->get($name);
    }
}
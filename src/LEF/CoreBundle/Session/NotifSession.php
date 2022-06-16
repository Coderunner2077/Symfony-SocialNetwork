<?php
// src/LEF/CoreBundle/Session/NotifSession.php

namespace LEF\CoreBundle\Session;

use Doctrine\ORM\EntityManagerInterface;
use LEF\CoreBundle\Context\AuthenticationContext;
use Symfony\Component\HttpFoundation\Session\Session;

class NotifSession {
    protected $authContext;
    protected $session;
    protected $em;
    const EXPIRE = 10 * 60;
    
    public function __construct(AuthenticationContext $authContext, Session $session, EntityManagerInterface $em) {
        $this->authContext = $authContext;
        $this->session = $session;
        $this->em = $em;
    }
    
    public function initialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
            
        if($this->session->has('article_notif_session'))
            return true;
                
        $this->startNotifSession();
        return true;
    }
    
    public function reinitialize() {
        if(!$this->authContext->isAuthenticated())
            return false;
             
        $this->startNotifSession();
        return true;
    }
    
    public function startNotifSession() {
        $this->startArticleNotifSession();
        $this->startGroupPostNotifSession();
        $this->startPostNotifSession();
    }
    public function startArticleNotifSession() {
        if(!$this->authContext->isAuthenticated())
            return false;
        $rep = $this->em->getRepository('LEFArticleBundle:ArticleNotification');
        $anIds = $rep->getArticleIds($this->authContext->getUser()->getId());
        $tab = [];
        foreach($anIds as $id) {
            $tab['notif_'. $id] = $id;
        }
        
        $this->session->set('article_notif_session', $tab);
        $this->session->set('article_notif_session_expire', time() + self::EXPIRE);
    }
    
    public function startGroupPostNotifSession() {
        if(!$this->authContext->isAuthenticated())
            return false;
        $rep = $this->em->getRepository('LEFGroupBundle:GroupPostNotification');
        $anIds = $rep->getGroupPostIds($this->authContext->getUser()->getId());
        $tab = [];
        foreach($anIds as $id) {
            $tab['notif_'. $id] = $id;
        }
        
        $this->session->set('group_post_notif_session', $tab);
        $this->session->set('group_post_notif_session_expire', time() + self::EXPIRE);
    }
    
    public function startPostNotifSession() {
        if(!$this->authContext->isAuthenticated())
            return false;
        $rep = $this->em->getRepository('LEFPostBundle:PostNotification');
        $anIds = $rep->getPostIds($this->authContext->getUser()->getId());
        $tab = [];
        foreach($anIds as $id) {
            $tab['notif_'. $id] = $id;
        }
        
        $this->session->set('post_notif_session', $tab);
        $this->session->set('post_notif_session_expire', time() + self::EXPIRE);
    }
    
    public function isExpired($name = null) {
        if(empty($name))
            $name = 'article';
        if($this->session->has($name . '_notif_session_expire') == false)
            return true;
        $expire = $this->session->get($name . '_notif_session_expire');
        if($expire < time())
            return true;
        return false;
    }
    
    public function setExpired($name, $time) {
        $this->session->set($name . '_notif_session_expire', time() + $time);
    }
    
    public function getExpired($name) {
        return $this->session->get($name . '_notif_session_expire');
    }
    
    public function countArticles($checkExpire = true) {
        if($checkExpire === true && $this->isExpired('article'))
            $this->startArticleNotifSession();
        return count($this->session->get('article_notif_session'));
    }
    
    public function countGroupPosts($checkExpire = true) {
        if($checkExpire === true && $this->isExpired('group_post'))
            $this->startGroupPostNotifSession();
        return count($this->session->get('group_post_notif_session'));
    }
    
    public function countPosts($checkExpire = true) {
        if($checkExpire === true && $this->isExpired('post'))
            $this->startPostNotifSession();
        return count($this->session->get('post_notif_session'));
    }
    
    public function add($name, $id) {
        $ids = $this->session->get($name . '_notif_session');
        $ids['notif_'.$id] = (int)$id;
        $this->session->set($name. '_notif_session', $ids);
    }
    
    public function remove($name, $id) {
        $session = $this->session->get($name . 'notif_session');
        unset($session['notif_' . $id]);
        $this->session->set($name. '_notif_session', $session);
    }
    
    public function has($name, $id, $checkExpire = true) {
        if($checkExpire === true && $this->isExpired($name))
            $this->reinitialize();
            
        return isset($this->session->get($name . '_notif_session')['notif_'.$id]);
    }
    
}
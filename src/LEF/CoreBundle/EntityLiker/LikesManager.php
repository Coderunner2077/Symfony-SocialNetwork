<?php
// src/LEF/CoreBundle/EntityLiker/LikesManager.php

namespace LEF\CoreBundle\EntityLiker;

use Doctrine\ORM\EntityManagerInterface;
use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use LEF\CoreBundle\Context\AuthenticationContext;
use LEF\CoreBundle\Component\ClassResolverTrait;   

class LikesManager {
    use ClassResolverTrait;
    
    protected $authContext;
    protected $likesChecker;
    protected $likesSession;
    protected $em;
    
    public function __construct(AuthenticationContext $context, LikesChecker $likesChecker, LikesSession $likesSession, EntityManagerInterface $em) {
        $this->authContext = $context;
        $this->likesChecker = $likesChecker;
        $this->likesSession = $likesSession;
        $this->em = $em;
    }       

    public function processLike($id, $attributeName) {
        if(!$this->authContext->isUser())
            return null;
        
        $class = $this->resolveClass($attributeName);
        if(empty($class))
            throw new LogicException('exception.logic.like');
        
        $likeSessionName = $attributeName . 'Like';
        $isLiked = $this->likesChecker->isLiked($attributeName, $id);
         
        $rep = $this->em->getRepository($class. 'Like');      
        
        if($isLiked === true) {
            $like = $rep->findLikeDislike($id, $this->authContext->getUser(), $attributeName, $class . 'Like');
            if(empty($like))
                throw new NotFoundHttpException('exception.notfound.lefcore.like');
                //throw new NotFoundHttpException('user : ' . $this->authContext->getUser()->getUsername()
                  //  . ', attr : ' . $attributeName . ', class : ' . $class);
            $this->em->remove($like);
            $this->likesSession->remove($likeSessionName, $id);
            $data = ['liked' => false, 'dislikeRemoved' => false];
        }
        else {
            $data = ['liked' => true];
            if($this->likesChecker->isDisliked($attributeName, $id) === true) {
                $dislike = $rep->findLikeDislike($id, $this->authContext->getUser(), $attributeName, $class. 'Dislike');
                if(empty($dislike))
                    throw new NotFoundHttpException('exception.notfound.lefcore.dislike');
                $this->em->remove($dislike);
                $this->likesSession->remove($attributeName . 'Dislike', $id);
                $data['dislikeRemoved'] = true;
            }
            $like = $class . 'Like';
            $entity = $this->em->getRepository($class)->find($id);
            if(empty($entity))
                throw new NotFoundHttpException('exception.notfound.lefcore.'. $attributeName);
            $like = new $like([$attributeName => $entity, 'user' => $this->authContext->getCurrentUser()]);
            $this->em->persist($like);
            $this->likesSession->add($likeSessionName, $id);       
        }
            
        $this->em->flush();
        
        return $data;        
    }
    
    public function processDislike($id, $attributeName) {
        if(!$this->authContext->isUser())
            return null;
            
        $class = $this->resolveClass($attributeName);
        if(empty($class))
            throw new LogicException('exception.logic.dislike');
                
        $isDisliked = $this->likesChecker->isDisliked($attributeName, $id);
        
        $dislikeSessionName = $attributeName . 'Dislike';
                
        $rep = $this->em->getRepository($class. 'Dislike');
                
        if($isDisliked === true) {
            $dislike = $rep->findLikeDislike($id, $this->authContext->getUser(), $attributeName, $class . 'Dislike');
            if(empty($dislike))
                throw new NotFoundHttpException('exception.notfound.lefcore.like');
                        
            $this->em->remove($dislike);
            $this->likesSession->remove($dislikeSessionName, $id);
            $data = ['disliked' => false, 'likeRemoved' => false];  
        } else {
              $data = ['disliked' => true];
              if($this->likesChecker->isLiked($attributeName, $id) === true) {
                  $like = $rep->findLikeDislike($id, $this->authContext->getUser(), $attributeName, $class. 'Like');
                  if(empty($like))
                      throw new NotFoundHttpException('exception.notfound.lefcore.like');
                  $this->em->remove($like);
                  $this->likesSession->remove($attributeName . 'Like', $id);
                  $data['likeRemoved'] = true;
              }
              $dislike = $class . 'Dislike';
              
              $entity = $this->em->getRepository($class)->find($id);
              if(empty($entity))
                  throw new NotFoundHttpException('exception.notfound.lefcore.'. $attributeName);
              $dislike = new $dislike([$attributeName => $entity, 'user' => $this->authContext->getCurrentUser()]);
              $this->em->persist($dislike);
              $this->likesSession->add($dislikeSessionName, $id);
          }
                
          $this->em->flush();
                
          return $data;
    }
}

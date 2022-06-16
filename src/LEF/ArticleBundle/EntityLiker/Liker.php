<?php
// src/LEF/ArticleBundle/EntityLiker/Liker.php

namespace LEF\ArticleBundle\EntityLiker;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

class Liker {
    protected $em;
    protected $reqStack;
    protected $translator;
    
    public function __construct(ObjectManager $em, RequestStack $reqStack, TranslatorInterface $trans) {
        $this->em = $em;
        $this->reqStack = $reqStack;
        $this->translator = $trans;
    }
    
    public function process(UserInterface $user, array $entityId, $liked = true, $className = 'Article') {
        $entity = $this->em->getResository('LEFArticleBundle:'.$className)->findOneWithLikes($entityId);
        if($entity === null)
            throw new NotFoundHttpException($this->translator->trans('notfound.'.lcfirst($className), array(), 'exception'));
        if($entity->getUser() === $user) {
            $this->reqStack->getCurrentRequest()->getFlashBag()->add('Notice', 'flash.info.likes');
            return $entity;
        }
        if($liked) {
            $liked = $this->em->getRepository('LEFArticleBundle:Liked'.$className)
                              ->findOneBy(array('user' => $user, lcfirst($className) => $entity));
            if($liked !== null) {
                $entity->removeLike($liked);
                $this->em->remove($liked);
                return $entity;
            }
            
            $disliked= $this->em->getRepository('LEFArticleBundle:Disliked'. $className)
                                ->findOneBy(array('user' => $user, lcfirst($className) => $entity));
            if($disliked !== null) {
                $entity->removeDislike($disliked);
                $this->em->remove($disliked);
            }
            $likedClass = '\LEF\ArticleBundle\Entity\Liked' . $className;
            $liked = new $likedClass([lcfirst($className) => $entity, 'user' => $user]);
            $this->em->persist($liked);
            $entity->addLike($liked);
            
            return $entity;
        } else {
            $disliked= $this->em->getRepository('LEFArticleBundle:Disliked'. $className)
                                ->findOneBy(array('user' => $user, lcfirst($className) => $entity));
            if($disliked !== null) {
                $entity->removeDislike($disliked);
                $this->em->remove($disliked);
                
                return $entity;
            }
            
            $liked = $this->em->getRepository('LEFArticleBundle:Liked'.$className)
                              ->findOneBy(array('user' => $user, lcfirst($className) => $entity));
            if($liked !== null) {
                $entity->removeLike($liked);
                $this->em->remove($liked);
            }
            
            $dislikedClass = '\LEF\ArticleBundle\Entity\Disliked' . $className;
            $disliked = new $dislikedClass([lcfirst($className) => $entity, 'user' => $user]);
            $this->em->persist($disliked);
            $entity->addLike($disliked);
            
            return $entity;
        }
    }
}


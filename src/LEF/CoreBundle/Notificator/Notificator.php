<?php
// src/LEF/CoreBundle/Notificator/Notificator.php

namespace LEF\CoreBundle\Notificator;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\Security\Core\Exception\LogicException;
use LEF\PostBundle\Entity\Post;

class Notificator {
    protected $em;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    
    public function resolveBaseClass(Entity $entity) {
        $baseName = [];
        preg_match('#\\\\([a-z]+)$#i', $entity->getClass(), $baseName);
        
        if(empty($baseName[1]))
            throw new LogicException('exception.logic.lefcore.likeschecker');
        return $baseName[1];
    }
    
    public function addNotification(Entity $entity) {
        $rep = $this->em->getRepository('LEFUserBundle:User');
        if($entity instanceof Post) {
            $ids = $rep->getFollowersIds($entity->getAuthor()->getId());
            $iterator = $rep->followersIterator($ids);
        } else {
            $ids = $rep->getGroupFollowersIds($entity->getGroup()->getId());
            $iterator = $rep->groupFollowersIterator($ids);
        }
        $entityClass = $this->resolveBaseClass($entity);
        $attribute = lcfirst($entityClass);
        $notifClass = $entity->getClass() . 'Notification';
        $batchSize = 20;
        $i = 0;
        while (($row = $iterator->next()) !== false) {
            $follower = $row[0];
            if($follower == $entity->getAuthor())
                continue;
            $notification = new $notifClass([$attribute => $entity, 'user' => $follower]);
            $this->em->persist($notification);
            if(($i % $batchSize) == 0) {
                $this->em->flush();
                $this->em->clear(); // Detaches all objects from Doctrine !
            }
            $i++;
        }
        $this->em->flush();
        $this->em->clear();
    }
}
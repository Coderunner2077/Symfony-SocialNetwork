<?php
// src/LEF/GroupBundle/Repository/GroupPostNotificationRepository.php

namespace LEF\GroupBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;

class GroupPostNotificationRepository extends EntityRepository {        
    public function findGroupPostNotifs($id, $limit = 10, $offset = 0, $viewed = null) {
        $qb =  $this->createQueryBuilder('gpn');
        $qb
        ->innerJoin('gpn.user', 'u', Join::WITH, $qb->expr()->eq('u.id', (int)$id))
        ->leftJoin('gpn.groupPost', 'gp')
        ->addSelect('gp')
        ->leftJoin('gp.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('gp.author', 'au')
        ->addSelect('au')     
        ->leftJoin('gp.editor', 'e')
        ->addSelect('e');
        if($viewed !== null) {
            $qb->where('gpn.viewed = :viewed')
            ->setParameter('viewed', $viewed);
        }
        $qb
        ->orderBy('gp.publishedAt', 'DESC')
        ->setMaxResults((int) $limit)
        ->setFirstResult($offset);
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);
        
        return $paginator->getIterator();
    }
    
    public function getGroupPostIds($id) {
        $result = $this->_em->createQuery('SELECT gp.id FROM LEFGroupBundle:GroupPostNotification gpn'
            . ' INNER JOIN gpn.user u WITH u.id = :id LEFT JOIN gpn.groupPost gp WHERE gpn.viewed = false')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
        return array_map(function($data) {
            return $data['id'];
        }, $result);
    }
    
    public function setViewed($userId, $postId) {
        $this->_em->createQuery('UPDATE LEFGroupBundle:GroupPostNotification gpn SET gpn.viewed = true'
            . ' WHERE gpn.user = :user AND gpn.groupPost = :post')
            ->setParameters(new ArrayCollection(array(
                new Parameter('post', $postId, 'integer'),
                new Parameter('user', $userId, 'integer')
            )))
            ->execute();
    }
}
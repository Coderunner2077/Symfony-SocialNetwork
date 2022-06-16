<?php
// src/LEF/PostBundle/Repository/EntityRepository.php

namespace LEF\PostBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository as BaseRepository;

class PostNotificationRepository extends BaseRepository {            
    public function findPostNotifs($id, $limit = 10, $offset = 0, $viewed = null) {
        $qb =  $this->createQueryBuilder('pn');
        $qb
        ->innerJoin('pn.user', 'u', Join::WITH, $qb->expr()->eq('u.id', (int)$id))
        ->leftJoin('pn.post', 'p')
        ->addSelect('p')      
        ->leftJoin('p.author', 'au')
        ->addSelect('au')
        ->leftJoin('p.editor', 'e')
        ->addSelect('e');
        if($viewed !== null) {
            $qb->where('pn.viewed = :viewed')
            ->setParameter('viewed', $viewed);
        }
        $qb
        ->orderBy('p.publishedAt', 'DESC')
        ->setMaxResults((int) $limit)
        ->setFirstResult($offset);
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);
        
        return $paginator->getIterator();
    }
    
    public function getPostIds($id) {
        $result = $this->_em->createQuery('SELECT p.id FROM LEFPostBundle:PostNotification pn'
            . ' INNER JOIN pn.user u WITH u.id = :id LEFT JOIN pn.post p WHERE pn.viewed = false')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
        return array_map(function($data) {
            return $data['id'];
        }, $result);
    }
    
    public function setViewed($userId, $postId) {
        $this->_em->createQuery('UPDATE LEFPostBundle:PostNotification pn SET pn.viewed = true'
            . ' WHERE pn.user = :user AND pn.post = :post')
            ->setParameters(new ArrayCollection(array(
                new Parameter('user', $userId, 'integer'),
                new Parameter('post', $postId, 'integer')
            )))
            ->execute();
    }
}
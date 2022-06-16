<?php
// src/LEF/ArticleBundle/Repository/EntityRepository.php

namespace LEF\ArticleBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository as BaseRepository;

class ArticleNotificationRepository extends BaseRepository {    
    public function findArticleNotifs($id, $limit = 4, $offset = 0, $viewed = null) {
        $qb =  $this->createQueryBuilder('an');
        $qb
        ->innerJoin('an.user', 'u', Join::WITH, $qb->expr()->eq('u.id', (int)$id))
        ->leftJoin('an.article', 'a')
        ->addSelect('a')
        ->innerJoin('a.author', 'au')
        ->addSelect('au')
        ->leftJoin('a.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('a.image', 'img')
        ->addSelect('img')
        ->innerJoin('a.category', 'cat')
        ->addSelect('cat')
        ->leftJoin('a.editor', 'e')
        ->addSelect('e')
        ->where('a.published = true');
        
        if($viewed !== null) {
            $qb->andWhere('an.viewed = :viewed')
               ->setParameter('viewed', $viewed);
        }
        $qb
        ->orderBy('a.publishedAt', 'DESC')
        ->setMaxResults((int)$limit)
        ->setFirstResult($offset);
        $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);
      
        return $paginator->getIterator();
    }
    
    public function getArticleIds($id) {
        $result = $this->_em->createQuery('SELECT a.id FROM LEFArticleBundle:ArticleNotification an'
            . ' INNER JOIN an.user u WITH u.id = :id LEFT JOIN an.article a WHERE an.viewed = false')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
        return array_map(function($data) {
            return $data['id'];
        }, $result);
    }
    
    public function setViewed($userId, $articleId) {
        $this->_em->createQuery('UPDATE LEFArticleBundle:ArticleNotification an SET an.viewed = true'
            . ' WHERE an.article = :article AND an.user = :user')
            ->setParameters(new ArrayCollection(array(
                new Parameter('article', $articleId, 'integer'),
                new Parameter('user', $userId, 'integer')
            )))
            ->execute();
    }
}
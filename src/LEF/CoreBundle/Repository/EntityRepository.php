<?php
// src/LEF/CoreBundle/Repository/EntityRepository.php

namespace LEF\CoreBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository as BaseRepository;
use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\Security\Core\User\UserInterface;

class EntityRepository extends BaseRepository {
    public function findLikeDislike($entity, UserInterface $user, $attributeName, $className) {
        $qb = $this->_em->createQueryBuilder();
        return $qb
                         ->select('ld')
                         ->from($className, 'ld')
                         ->innerJoin('ld.'.$attributeName, 'e', Join::WITH, $qb->expr()->in('e.id', ':id'))
                         ->setParameter('id', $entity)
                         ->where('ld.user = :user')
                         ->setParameter('user', $user)
                         ->getQuery()
                         ->getOneOrNullResult();
        
       
    }
    
    public function findLikes($id) {
        $likes = array();
        $likes['article'] = array_map(function($data) {
            return $data['id'];
        }, $this->findArticleLikes($id));
        
        $likes['article'] =  new ArrayCollection(array_combine(array_values($likes['article']), $likes['article']));
        
        $likes['comment'] = array_map(function($data) {
            return $data['id'];
        }, $this->findArticleLikes($id));
        
        $likes['comment'] = new ArrayCollection(array_combine(array_values($likes['comment']), $likes['comment']));
        
        $likes['group_post'] = array_map(function($data) {
            return $data['id'];
        }, $this->findGroupPostLikes($id));
            
        $likes['group_post'] = new ArrayCollection(array_combine(array_values($likes['group_post']), $likes['group_post']));
        
        $likes['post'] = array_map(function($data) {
            return $data['id'];
        }, $this->findPostLikes($id));
            
        $likes['post'] = new ArrayCollection(array_combine(array_values($likes['post']), $likes['post']));
            
        return $likes;
    }
    
    public function findDislikes($id) {
        $dislikes = array();
        $dislikes['article'] = array_map(function($data) {
            return $data['id'];
        }, $this->findArticleDislikes($id));
        
        $dislikes['article'] =  new ArrayCollection(array_combine(array_values($dislikes['article']), $dislikes['article']));
            
        $dislikes['comment'] = array_map(function($data) {
            return $data['id'];
        }, $this->findArticleDislikes($id));
            
        $dislikes['comment'] = new ArrayCollection(array_combine(array_values($dislikes['comment']), $dislikes['comment']));
            
        $dislikes['group_post'] = array_map(function($data) {
                return $data['id'];
        }, $this->findGroupPostDislikes($id));
                
        $dislikes['group_post'] = new ArrayCollection(array_combine(array_values($dislikes['group_post']), $dislikes['group_post']));
                
        $dislikes['post'] = array_map(function($data) {
                    return $data['id'];
        }, $this->findPostDislikes($id));
                    
        $dislikes['post'] = new ArrayCollection(array_combine(array_values($dislikes['post']), $dislikes['post']));
        
        return $dislikes;
    }
    
    public function findArticleLikes($id) {
        return $this->_em->createQuery('SELECT art.id FROM LEFArticleBundle:ArticleLike a INNER JOIN a.user u WITH u.id = :id'
                        . ' LEFT JOIN a.article AS art')
                    ->setParameter('id', $id)
                    ->getScalarResult();
    }
    
    public function findArticleDislikes($id) {
        return $this->_em->createQuery('SELECT art.id FROM LEFArticleBundle:ArticleDislike a INNER JOIN a.user u WITH u.id = :id'
            . ' LEFT JOIN a.article AS art')
            ->setParameter('id', $id)
            ->getScalarResult();
    }
    
    public function findCommentLikes($id) {
        return $this->_em->createQuery('SELECT comm.id FROM LEFArticleBundle:CommentLike cl INNER JOIN cl.user u WITH u.id = :id'
            . ' LEFT JOIN cl.comment AS comm')
            ->setParameter('id', $id)
            ->getScalarResult();
    }
    
    public function findCommentDislikes($id) {
        return $this->_em->createQuery('SELECT comm.id FROM LEFArticleBundle:CommentDislike cl INNER JOIN cl.user u WITH u.id = :id'
            . ' LEFT JOIN cl.comment AS comm')
            ->setParameter('id', $id)
            ->getScalarResult();
    }
    
    public function findGroupPostLikes($id) {
        return $this->_em->createQuery('SELECT gr.id FROM LEFGroupBundle:GroupPostLike a INNER JOIN a.user u WITH u.id = :id'
            . ' LEFT JOIN a.groupPost AS gr')
            ->setParameter('id', $id)
            ->getScalarResult();
    }
    
    public function findGroupPostDislikes($id) {
        return $this->_em->createQuery('SELECT gr.id FROM LEFGroupBundle:GroupPostDislike a INNER JOIN a.user u WITH u.id = :id'
            . ' LEFT JOIN a.groupPost AS gr')
            ->setParameter('id', $id)
            ->getScalarResult();
    }
    
    public function findPostLikes($id) {
        return $this->_em->createQuery('SELECT p.id FROM LEFPostBundle:PostLike a INNER JOIN a.user u WITH u.id = :id'
            . ' LEFT JOIN a.post AS p')
            ->setParameter('id', $id) 
            ->getScalarResult();
    }
    
    public function findPostDislikes($id) {
        return $this->_em->createQuery('SELECT p.id FROM LEFPostBundle:PostDislike a INNER JOIN a.user u WITH u.id = :id'
            . ' LEFT JOIN a.post AS p')
            ->setParameter('id', $id)
            ->getScalarResult();
    }
}
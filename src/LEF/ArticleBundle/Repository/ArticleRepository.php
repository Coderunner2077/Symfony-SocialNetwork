<?php
// src/LEF/ArticleBundle/Repository/ArticleRepository.php

namespace LEF\ArticleBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;

class ArticleRepository extends \Doctrine\ORM\EntityRepository {
    public function findWithAll(array $orderBy, $limit = null, $offset = null) {
        $qb =  $this->createQueryBuilder('a');
        $qb
                    ->innerJoin('a.author', 'au')
                    ->addSelect('au')
                    ->leftJoin('au.avatar', 'av')
                    ->addSelect('av')
                    ->leftJoin('a.group', 'gr')
                    ->addSelect('gr')
                    ->leftJoin('gr.logo', 'lg')
                    ->addSelect('lg')
                    ->leftJoin('a.image', 'img')
                    ->addSelect('img')
                    ->innerJoin('a.category', 'cat')
                    ->addSelect('cat')
                    ->leftJoin('a.editor', 'e')
                    ->addSelect('e')
                    //->groupBy('cat')
                    ->where('a.published = true')
                    ->andWhere('a.publishedAt BETWEEN :start AND :end')
                    ->setParameters(new ArrayCollection(array (
                        new Parameter('start', new \DateTime('48 hours ago')),
                        new Parameter('end', new \DateTime())
                    )));
          $i = 0;
          foreach($orderBy as $sort => $order) {
              if($i++ < 1) $qb->orderBy('a.' .$sort, $order);
              else $qb->addOrderBy('a.'.$sort, $order);
          }
              
          if(!empty($limit)) {
              
              $qb->setMaxResults((int) $limit);
              if(!empty($offset))
                  $qb->setFirstResult((int) $offset);
                  
              $query = $qb->getQuery();
              $paginator = new Paginator($query, $fetchJoinCollection = true);
          }
          return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();         
    }
    
    public function myfindByGroup($id, array $orderBy = array(), $limit = null, $offset = null, $published = true) {
        $qb =  $this->createQueryBuilder('a');
        $qb->innerJoin('a.group', 'gr', Join::WITH, $qb->expr()->eq('gr.id', (int) $id))
        ->addSelect('gr')
        ->leftJoin('a.author', 'au')
        ->addSelect('au')
        ->leftJoin('a.image', 'img')
        ->addSelect('img')
        ->innerJoin('a.category', 'cat')
        ->addSelect('cat');
        //->groupBy('cat');
        if(is_bool($published)) {
            $qb->where('a.published = :bool');
            $qb->setParameter('bool', $published);
        }
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('a.' .$sort, $order);
            else $qb->addOrderBy('a.'.$sort, $order);
            
            if(!empty($limit)) {
                $qb->setMaxResults((int) $limit);
                if(!empty($offset))
                    $qb->setFirstResult((int) $offset);
                $query = $qb->getQuery();
                $paginator = new Paginator($query, $fetchJoinCollection = true);
            }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();
    }
    
    public function countPublished() {
        return $this->createQueryBuilder('a')
        ->select('COUNT(a)')
        ->where('a.published = true')
        ->andWhere('a.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('48 hours ago')),
            new Parameter('end', new \DateTime())
        )))
        ->getQuery()
        ->getSingleScalarResult();
    }
    
    public function getByCategory($id, array $orderBy, $limit = null, $offset = null) {
        $qb =  $this->createQueryBuilder('a');
        $qb
        ->innerJoin('a.author', 'au')
        ->addSelect('au')
        ->leftJoin('a.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('a.image', 'img')
        ->addSelect('img')
        ->innerJoin('a.category', 'cat', Join::WITH, $qb->expr()->eq('cat.id', $id))
        ->addSelect('cat')
        //->groupBy('cat')
        ->where('a.published = true')
        ->andWhere('a.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('48 hours ago')),
            new Parameter('end', new \DateTime())
        )));
        $i = 0;
        foreach($orderBy as $sort => $order) {
            if($i++ < 1) $qb->orderBy('a.' .$sort, $order);
            else $qb->addOrderBy('a.'.$sort, $order);
        }
        
        if(!empty($limit)) {
            
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
                
                $query = $qb->getQuery();
                $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();
    }
    
    public function getByGroupCategory($id, array $orderBy, $limit = null, $offset = null) {
        $qb =  $this->createQueryBuilder('a');
        $qb
        ->innerJoin('a.group', 'gr', Join::WITH, $qb->expr()->eq('gr.groupCategory', $id))
        ->addSelect('gr')
        ->leftJoin('a.author', 'au')
        ->addSelect('au')
        ->leftJoin('a.image', 'img')
        ->addSelect('img')
        ->leftJoin('a.category', 'cat')
        ->addSelect('cat')
        //->groupBy('cat')
        ->where('a.published = true')
        ->andWhere('a.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('9948 hours ago')),
            new Parameter('end', new \DateTime())
        )));
        $i = 0;
        foreach($orderBy as $sort => $order) {
            if($i++ < 1) $qb->orderBy('a.' .$sort, $order);
            else $qb->addOrderBy('a.'.$sort, $order);
        }
        
        if(!empty($limit)) {
            
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
                
                $query = $qb->getQuery();
                $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();
    }
    
    
    public function countByCategory($id) {
        return $this->createQueryBuilder('a')
        ->select('COUNT(a)')
        ->where('a.published = true')
        ->andWhere('a.category = :id')
        ->andWhere('a.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('id', $id),
            new Parameter('start', new \DateTime('48 hours ago')),
            new Parameter('end', new \DateTime())
        )))
        ->getQuery()
        ->getSingleScalarResult();
    }
    
    public function countByGroupCategory($id) {
        return $this->createQueryBuilder('a')
        ->select('COUNT(a)')
        ->innerJoin('a.group', 'gr', Join::WITH, 'gr.groupCategory = :id')
        ->where('a.published = true')
        ->andWhere('a.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('id', $id),
            new Parameter('start', new \DateTime('9948 hours ago')),
            new Parameter('end', new \DateTime())
        )))
        ->getQuery()
        ->getSingleScalarResult();
    }
    
    public function getViewCheckData($id) {
        return $this->createQueryBuilder('a')
                    ->select('a.public')
                    ->addSelect('a.published')
                    ->addSelect('a.group')
                    ->addSelect('a.author')
                    ->where('a.id = :id')
                    ->setParameter('id', (int) $id)
                    ->getQuery()
                    ->getScalarResult();
    }
    
    public function findOneWithAll($id) {
        return $this->createQueryBuilder('a')
                    ->innerJoin('a.author', 'au')
                    ->addSelect('au')
                    ->innerJoin('a.group', 'gr')
                    ->addSelect('gr')
                    ->leftJoin('gr.logo', 'lo')
                    ->addSelect('lo')
                    ->leftJoin('a.image', 'img')
                    ->addSelect('img')
                    ->innerJoin('a.category', 'cat')
                    ->addSelect('cat')
                    ->where('a.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
    
    public function findByGroupWithAll($id, array $orderBy, $limit = null, $offset = null) {
        $qb =  $this->createQueryBuilder('a');
        $qb
        ->innerJoin('a.group', 'gr', Join::WITH, $qb->expr()->eq('gr.id', (int) $id))
        ->addSelect('gr')
        ->leftJoin('a.author', 'au')
        ->addSelect('au')
        ->leftJoin('a.image', 'img')
        ->addSelect('img')
        ->innerJoin('a.category', 'cat')
        ->addSelect('cat')
        ->where('a.published = true')
        ->andWhere('a.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('24 years ago')),
            new Parameter('end', new \DateTime())
            
            
        )));
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('a.' .$sort, $order);
            else $qb->addOrderBy('a.'.$sort, $order);
            
            if(!empty($limit)) {
                
                $qb->setMaxResults((int) $limit);
                if(!empty($offset))
                    $qb->setFirstResult((int) $offset);
                    
                    $query = $qb->getQuery();
                    $paginator = new Paginator($query, $fetchJoinCollection = true);
            }
            return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();
    }
    
    public function findGroup($id) {
        return $this->_em->createQuery('SELECT g FROM LEFArticleBundle:Article a INNER JOIN a.group g '
               . ' WHERE a.id = :id')
               ->setParameter('id', (int) $id)
               ->getSingleResult();
    }
    
    public function findOneWithComments($id) {
        $qb = $this->createQueryBuilder('a');
        return $qb
        ->innerJoin('a.author', 'au')
        ->addSelect('au')
        ->innerJoin('a.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('a.comments', 'c', Join::WITH, $qb->expr()->eq('c.lvl', 0))
        ->addSelect('c')
        ->leftJoin('c.author', 'cau')
        ->addSelect('cau')
        ->leftJoin('c.editor', 'ced')
        ->addSelect('ced')
        ->where('a.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function searchArticles($patterns, array $orderBy = array(), $limit = null, $offset = null, $period = 0, array $notIds = array(), $orSearch = false) {
        $qb =  $this->createQueryBuilder('a');
        $qb->leftJoin('a.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('gr.groupCategory', 'grCat')
        ->addSelect('grCat')
        ->leftJoin('gr.logo', 'lg')
        ->addSelect('lg')
        ->leftJoin('a.author', 'au')
        ->addSelect('au')
        ->leftJoin('a.image', 'img')
        ->addSelect('img')
        ->innerJoin('a.category', 'cat')
        ->addSelect('cat')
        ->where('a.published = true');
        //->groupBy('cat');
        $params = [];
        if($period > 0) {
            $qb->andWhere('a.publishedAt >= :published');
            $params[] = new Parameter('published', new \DateTime($period . ' day ago'));
        }
        if($orSearch) {
            if(!empty($notIds)) {
                $qb->andWhere('a.id NOT IN(:ids)');
                $params[] = new Parameter('ids', $notIds);
            }
            $i = 0; $likeSql = '';
            foreach($patterns as $pattern) {
                if($i == 0) $likeSql .= 'a.title LIKE :param_' . $i;
                else $likeSql .= ' OR a.title LIKE :param_' . $i;
                $params[] = new Parameter('param_' . $i, '%'.$pattern . '%');
                $i++;
            }
            $qb->andWhere($likeSql)
               ->setParameters(new ArrayCollection($params));
        } else {
            $i = 0;
            foreach($patterns as $pattern) {
                $qb->andWhere($qb->expr()->like('a.title', ':param_' . $i));
                $params[] = new Parameter('param_' . $i, '%'.$pattern . '%');
                $i++;
            }
            $qb->setParameters(new ArrayCollection($params));
        }
            
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('a.' .$sort, $order);
            else $qb->addOrderBy('a.'.$sort, $order);
            
        if(!empty($limit)) {
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
            $query = $qb->getQuery();
            $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator()->getArrayCopy();
    }
    
    public function countSearchResult($patterns, $period = 0, array $notIds = array(), $orSearch = false) {
        $qb =  $this->createQueryBuilder('a');
        $qb->select('COUNT(a)')
        ->where('a.published = true');
        //->groupBy('cat');
        $params = [];
        if($period > 0) {
            $qb->andWhere('a.publishedAt >= :published');
            $params[] = new Parameter('published', new \DateTime($period . ' day ago'));
        }
        if($orSearch) {
            if(!empty($notIds)) {
                $qb->andWhere('a.id NOT IN(:ids)');
                $params[] = new Parameter('ids', $notIds);
            }
            $i = 0; $likeSql = '';
            foreach($patterns as $pattern) {
                if($i == 0) $likeSql .= 'a.title LIKE :param_' . $i;
                else $likeSql .= ' OR a.title LIKE :param_' . $i;
                $params[] = new Parameter('param_' . $i, '%'.$pattern . '%');
                $i++;
            }
            $qb->andWhere($likeSql)
            ->setParameters(new ArrayCollection($params));
        } else {
            $i = 0;
            foreach($patterns as $pattern) {
                $qb->andWhere($qb->expr()->like('a.title', ':param_' . $i));
                $params[] = new Parameter('param_' . $i, '%'.$pattern . '%');
                $i++;
            }
            
            $qb->setParameters(new ArrayCollection($params));
        }
        
       
        return $qb->getQuery()->getSingleScalarResult();
    }
}
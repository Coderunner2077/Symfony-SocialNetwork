<?php
// src/LEF/GroupBundle/Repository/GroupPostRepository.php

namespace LEF\GroupBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class GroupPostRepository extends NestedTreeRepository {
    public function findWithAll(array $orderBy = array(), $limit = null, $offset = null) {
        $qb= $this->createQueryBuilder('gp');
        $qb->leftJoin('gp.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('gr.logo', 'lg')
        ->addSelect('lg')
        ->leftJoin('gr.groupCategory', 'grcat')
        ->addSelect('grcat')
        ->leftJoin('gp.author', 'au')
        ->addSelect('au')   
        ->leftJoin('au.avatar', 'av')
        ->addSelect('av')
        ->leftJoin('gp.editor', 'e')
        ->addSelect('e')
        ->where('gp.publicPost = true')
        ->andWhere('gp.lvl = 0')
        ->andwhere('gp.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('4472 hours ago')),
            new Parameter('end', new \DateTime())
                    
         )));
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('gp.' .$sort, $order);
            else $qb->addOrderBy('gp.'.$sort, $order);
        
        if(!empty($limit)) {
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
            $query = $qb->getQuery();
            $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();         
    }
    
    public function myfindByGroup($id, array $orderBy = array(), $limit = null, $offset = null, $public = true) {
        $qb= $this->createQueryBuilder('gp');
        $qb->innerJoin('gp.group', 'gr', Join::WITH, $qb->expr()->eq('gr.id', (int) $id))
           ->addSelect('gr')
           ->leftJoin('gp.author', 'au')
           ->addSelect('au')
           ->where('gp.lvl = 0 OR gp.repost = true');
        if(is_bool($public)) {
            $qb->andWhere('gp.publicPost = :bool');
            $qb->setParameter('bool', $public);
        }
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('gp.' .$sort, $order);
            else $qb->addOrderBy('gp.'.$sort, $order);
                    
        if(!empty($limit)) {
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
            $query = $qb->getQuery();
            $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();
    }
    
    public function myFindByCategory($id, array $orderBy = array(), $limit = null, $offset = null) {
        $qb= $this->createQueryBuilder('gp');
        $qb->innerJoin('gp.author', 'au')
        ->addSelect('au')
        ->innerJoin('gp.group', 'gr', Join::WITH, $qb->expr()->eq('gr.groupCategory', $id))
        ->addSelect('gr')
        ->where('gp.publicPost = true')
        ->andWhere('gp.lvl = 0')
        ->andwhere('gp.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('4472 hours ago')),
            new Parameter('end', new \DateTime())
            
        )));
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('gp.' .$sort, $order);
            else $qb->addOrderBy('gp.'.$sort, $order);
            
            if(!empty($limit)) {
                $qb->setMaxResults((int) $limit);
                if(!empty($offset))
                    $qb->setFirstResult((int) $offset);
                    $query = $qb->getQuery();
                    $paginator = new Paginator($query, $fetchJoinCollection = true);
            }
            return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();
    }
    
    public function findOneWithAll($id) {
        $qb = $this->createQueryBuilder('gp');
        return $qb
        ->leftJoin('gp.author', 'au')
        ->addSelect('au')
        ->leftJoin('gp.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('gr.groupCategory', 'cat')
        ->addSelect('cat')
        ->leftJoin('gr.background', 'bg')
        ->addSelect('bg')
        ->leftJoin('gr.logo', 'lg')
        ->addSelect('lg')
        ->leftJoin('gp.children', 'ch', Join::WITH, $qb->expr()->eq('ch.banned', 0))
        ->addSelect('ch')
        ->leftJoin('ch.author', 'chau')
        ->addSelect('chau')
        ->leftJoin('gp.parent', 'pt')
        ->addSelect('pt')
        ->leftJoin('pt.author', 'ptau')
        ->addSelect('ptau')/*
        ->leftJoin('pt.group', 'ptgr')
        ->addSelect('ptgr')
        ->leftJoin('ptgr.groupCategory', 'ptcat')
        ->addSelect('ptcat')
        ->leftJoin('ptgr.logo', 'ptgrlg')
        ->addSelect('ptgrlg')*/
        ->where('gp.id = :id')
        ->setParameter('id', (int) $id)
        ->orderBy('ch.likes', 'DESC')
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function findOneWithGroup($id) {
        $qb = $this->createQueryBuilder('gp');
        return $qb
        ->leftJoin('gp.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('gr.logo', 'lg')
        ->addSelect('lg')
        ->where('gp.id = :id')
        ->setParameter('id', (int) $id)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function countPosts() {
        return $this->createQueryBuilder('g')
        ->select('COUNT(g)')
        ->where('g.publicPost = true')
        ->andWhere('g.lvl = 0')
        ->andwhere('g.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('4472 hours ago')),
            new Parameter('end', new \DateTime())
            
        )))
        ->getQuery()
        ->getSingleScalarResult();
    }
    
    public function countPostsByCategory($id) {
        return $this->createQueryBuilder('g')
        ->select('COUNT(g)')
        ->innerJoin('g.group', 'gr', Join::WITH, 'gr.groupCategory = :id')
        ->where('g.publicPost = true')
        ->andWhere('g.lvl = 0')
        ->andwhere('g.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('id', $id),
            new Parameter('start', new \DateTime('4472 hours ago')),
            new Parameter('end', new \DateTime())
            
        )))
        ->getQuery()
        ->getSingleScalarResult();
    }
}
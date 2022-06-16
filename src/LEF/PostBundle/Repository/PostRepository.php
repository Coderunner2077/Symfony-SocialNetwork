<?php
// src/LEF/PostBundle/Repository/PostRepository.php

namespace LEF\PostBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class PostRepository extends NestedTreeRepository {
    public function findWithAll(array $orderBy = array(), $limit = null, $offset = null) {
        $qb= $this->createQueryBuilder('p');
        $qb
        ->leftJoin('p.author', 'au')
        ->addSelect('au')      
        ->leftJoin('au.avatar', 'av')
        ->addSelect('av')
        ->leftJoin('p.editor', 'e')
        ->addSelect('e')
        ->leftJoin('p.image', 'i')
        ->addSelect('i')
        ->where('p.lvl = 0 OR p.repost = true')
        ->andwhere('p.publishedAt BETWEEN :start AND :end')
        ->setParameters(new ArrayCollection(array (
            new Parameter('start', new \DateTime('4472 hours ago')),
            new Parameter('end', new \DateTime())
                    
         )));
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('p.' .$sort, $order);
            else $qb->addOrderBy('p.'.$sort, $order);
        
        if(!empty($limit)) {
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
            $query = $qb->getQuery();
            $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator();         
    }
 
    public function myfindByAuthor($id, array $orderBy = array(), $limit = null, $offset = null) {
        $qb= $this->createQueryBuilder('p');
        $qb->innerJoin('p.author', 'au', Join::WITH, $qb->expr()->eq('au.id', (int) $id))
           ->addSelect('au')
           ->leftJoin('p.image', 'i')
           ->addSelect('i')
           ->where('p.lvl = 0 OR p.repost = true');
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('p.' .$sort, $order);
            else $qb->addOrderBy('p.'.$sort, $order);
                    
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
        $qb = $this->createQueryBuilder('p');
        return $qb
        ->leftJoin('p.author', 'au')
        ->addSelect('au')
        ->leftJoin('p.children', 'ch', Join::WITH, $qb->expr()->eq('ch.banned', 0))
        ->addSelect('ch')
        ->leftJoin('ch.author', 'chau')
        ->addSelect('chau')
        ->leftJoin('p.parent', 'pt')
        ->addSelect('pt')
        ->leftJoin('pt.author', 'ptau')
        ->addSelect('ptau')
        ->where('p.id = :id')
        ->setParameter('id', (int) $id)
        ->orderBy('ch.likes', 'DESC')
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function findOneWithAuthor($id) {
        $qb = $this->createQueryBuilder('p');
        return $qb
        ->leftJoin('p.au', 'au')
        ->addSelect('au')
        ->where('p.id = :id')
        ->setParameter('id', (int) $id)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function findOneWithParent($id) {
        $qb = $this->createQueryBuilder('p');
        return $qb
        ->leftJoin('p.author', 'au')
        ->addSelect('au')
        ->leftJoin('p.parent', 'prt')
        ->addSelect('prt')
        ->leftJoin('prt.author', 'prtau')
        ->addSelect('prtau')
        ->where('p.id = :id')
        ->setParameter('id', (int) $id)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function searchPosts($pattern, array $orderBy = array(), $limit = null, $offset = null) {
        $qb =  $this->createQueryBuilder('p');
        $qb
        ->leftJoin('p.author', 'au')
        ->addSelect('au')
        ->leftJoin('au.avatar', 'av')
        ->addSelect('av')
        ->leftJoin('p.editor', 'e')
        ->addSelect('e')
        ->leftJoin('p.image', 'i')
        ->addSelect('i')
        ->where('p.lvl < 3');
        $params = [];
        //$qb->andWhere($qb->expr()->like('p.content', ':hashtag'));
        $qb->andWhere('REGEXP(p.content, :hashtag) = true');
        $params[] = new Parameter('hashtag', $pattern . '[^:alnum:]|'.$pattern . '$');
        $qb->setParameters(new ArrayCollection($params));
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('p.' .$sort, $order);
            else $qb->addOrderBy('p.'.$sort, $order);
            
        if(!empty($limit)) {
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
            $query = $qb->getQuery();
            $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator()->getArrayCopy();      
    }
    
    public function countSearchResult($pattern) {
        $qb =  $this->createQueryBuilder('p');
        $qb->select('COUNT(p)');
        $params = [];
        //$qb->where($qb->expr()->like('p.content', ':hashtag'));
        $qb->where('REGEXP(p.content, :hashtag) = true');
        
        $params[] = new Parameter('hashtag', $pattern . '[^:alnum:]|'.$pattern . '$');
        $qb->setParameters(new ArrayCollection($params));
        
        return $qb->getQuery()->getSingleScalarResult();
    }
}
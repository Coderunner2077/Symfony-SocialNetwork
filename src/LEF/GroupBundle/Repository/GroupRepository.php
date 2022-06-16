<?php
// src/LEF/GroupBundle/Repository/GroupRepository.php

namespace LEF\GroupBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;


class GroupRepository extends NestedTreeRepository {
    public function getUpdatedAt($id) {
        return $this->createQueryBuilder('g')
                    ->select('g.updatedAt')
                    ->where('g.id = :id')
                    ->setParameter('id', (int) $id)
                    ->getQuery()
                    ->getSingleScalarResult();
    }
    
    public function findOneWithAll($id) {
        $qb = $this->createQueryBuilder('g');
        return $qb
                    ->leftJoin('g.groupCategory', 'gc')
                    ->addSelect('gc')
                    ->leftJoin('g.background', 'bg')
                    ->addSelect('bg')
                    ->leftJoin('g.logo', 'lg')
                    ->addSelect('lg')
                    ->leftJoin('g.root', 'rt')
                    ->addSelect('rt')
                    ->leftJoin('rt.logo', 'rlg')
                    ->addSelect('rlg')
                    ->leftJoin('g.parent', 'prt')
                    ->addSelect('prt')
                    ->leftJoin('prt.logo', 'plg')
                    ->addSelect('plg')
                    ->where('g.id = :id')
                    ->setParameter('id', (int) $id)
                    ->getQuery()
                    ->getOneOrNullResult();      
    }
    
    public function findWithAll(array $orderBy = array(), $limit = null, $offset = null) {
        $qb = $this->createQueryBuilder('g');
        $qb
        ->leftJoin('g.groupCategory', 'gc')
        ->addSelect('gc')
        ->leftJoin('g.logo', 'lg')
        ->addSelect('lg')
        ->groupBy('gc')
        ->where('g.lvl = 1')
        ->andWhere('g.enabled = true');
        
        $i = 0;
        foreach($orderBy as $sort => $order) {
            if($i++ < 1) $qb->orderBy('g.' . $sort, $order);
            else $qb->addOrderBy('g.' . $sort, $order);
        }
        if(!empty($limit)) {
            $qb->setMaxResults($limit);
            if(!empty($offset))
                $qb->setFirstResult($offset);
            $paginator = new Paginator($qb, $fetchWithStuff = true);
        }
        
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator(); 
    }
    
    public function findGroups(array $ids, array $orderBy = array()) {
        $qb = $this->createQueryBuilder('g');
        $qb
        ->leftJoin('g.groupCategory', 'gc')
        ->addSelect('gc')
        ->leftJoin('g.logo', 'lg')
        ->addSelect('lg')
        ->where('g.id IN(:ids)')
        ->setParameter('ids', $ids);
        
        $i = 0;
        foreach($orderBy as $sort => $order) {
            if($i++ < 1) $qb->orderBy('g.' . $sort, $order);
            else $qb->addOrderBy('g.' . $sort, $order);
        }
      
        return $qb->getQuery()->getResult();
    }
    
    public function countGroups() {
        return $this->createQueryBuilder('g')
                    ->select('COUNT(g)')
                    ->where('g.lvl = 1')
                    ->getQuery()
                    ->getResult();
    }
}
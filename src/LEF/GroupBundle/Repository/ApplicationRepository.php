<?php
// src/LEF/GroupBundle/Repository/ApplicationRepository.php

namespace LEF\GroupBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr; 

class ApplicationRepository extends EntityRepository {
    public function findOneWithUserGroup($userId, $groupId) {
        $qb = $this->createQueryBuilder('a');
        return $qb->innerJoin('a.group', 'g', Join::WITH, $qb->expr()->eq('g.id', ':group'))
                  ->innerJoin('a.applicant', 'apl', Join::WITH, $qb->expr()->eq('apl.id', ':user'))
                  ->addSelect('apl')
                  ->leftJoin('apl.avatar', 'av')
                  ->addSelect('av')
                  ->setParameters(new ArrayCollection(array(
                      new Parameter('group', $groupId),
                      new Parameter('user', $userId)
                  )))
                  ->getQuery()
                  ->getOneOrNullResult();
    }
    
    public function findOneWithAll($id) {
        $qb = $this->createQueryBuilder('a');
        return 
        $qb->leftJoin('a.group', 'g')
           ->addSelect('g')
           ->leftJoin('a.applicant', 'apl')
           ->addSelect('apl')
           ->leftJoin('apl.avatar', 'av')
           ->addSelect('av')
           ->where('a.id = :id')
           ->setParameter('id', $id)
           ->getQuery()
           ->getOneOrNullResult();
    }
    
    public function findWithAllBy(array $data, $withRefused = false) {
        $qb = $this->createQueryBuilder('a');
        if(isset($data['group'])) {
            $qb->innerJoin('a.group', 'g', Join::WITH, $qb->expr()->eq('g.id', ':group'))
               ->addSelect('g')
               ->setParameter('group', $data['group']);
        } elseif(isset($data['groups'])) {
            $qb->innerJoin('a.group', 'g', Join::WITH, $qb->expr()->in('g.id', ':groups'))
               ->addSelect('g')
               ->setParameter('groups', $data['groups']);
        } else {
            $qb->leftJoin('a.group', 'g')
            ->addSelect('g');
        }
        if(isset($data['applicant'])) {
            $qb->innerJoin('a.applicant', 'apl', Join::WITH, $qb->expr()->eq('apl.id', ':apl'))
               ->addSelect('apl')
               ->setParameter('apl', $data['applicant'])
               ->leftJoin('apl.avatar', 'av')
               ->addSelect('av');
        } else {
            $qb->leftJoin('a.applicant', 'apl')
               ->addSelect('apl')
               ->leftJoin('apl.avatar', 'av')
               ->addSelect('av');
        }
        
        if(!$withRefused)
            $qb->where('a.status <> 128');
        $qb->orderBy('a.requestedAt', 'ASC');
            
        return $qb->getQuery()->getResult();
    }
    
    public function getIds($id, $offerOnly = true) {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a.id')
                  ->where('a.applicant = :id')
                  ->setParameter('id', $id, 'integer');
        if($offerOnly)
            $qb->andWhere('a.status = 2 OR a.status = 8');
        
        $res = $qb->getQuery()->getResult();
        return array_map(function($data) {
            return $data['id'];
        }, $res);
    }
    
    public function getAppIds(array $ids, $offerOnly = true) {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a.id')
        ->where('a.group IN(:ids)')
        ->setParameter('ids', $ids);
        if($offerOnly)
            $qb->andWhere('a.status = 1 OR a.status = 4');
            
        $res = $qb->getQuery()->getResult();
        return array_map(function($data) {
            return $data['id'];
        }, $res);
    }
    
    public function findWithGroup($userId, array $groups, $withRefused = false) {
        $qb = $this->createQueryBuilder('a');
        $qb->innerJoin('a.group', 'g', Join::WITH, $qb->expr()->in('g.id', ':groups'))
            ->addSelect('g')
            ->setParameter('groups', $groups)
            ->innerJoin('a.applicant', 'apl', Join::WITH, $qb->expr()->eq('apl.id', ':apl'))
            ->setParameter('apl', $userId);
        if(!$withRefused)
            $qb->where('a.status <> 128');
        $qb->orderBy('a.requestedAt', 'ASC');
            
        return $qb->getQuery()->getResult();
    }
}
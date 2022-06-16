<?php
// src/LEF/GroupBundle/Repository/MemberPrivilegeRepository.php

namespace LEF\GroupBundle\Repository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr; 

class MemberPrivilegeRepository extends EntityRepository {
    public function findPrivileges($id) {
        $qb = $this->createQueryBuilder('mp');
        return $qb->innerJoin('mp.member', 'm', Join::WITH, $qb->expr()->eq('m.id', (int) $id))
                  ->addSelect('m')
                  ->innerJoin('mp.group', 'gr')
                  ->addSelect('gr')
                  ->getQuery()
                  ->getResult();
    }
    
    public function getCardinals($id) {
        $qb = $this->createQueryBuilder('mp');
        return $qb->select('gr.id, gr.updatedAt, mp.masks')
        ->leftJoin('mp.group', 'gr')
        ->where('mp.member = :id')
        ->setParameter('id', (int) $id)
        ->getQuery()
        ->getArrayResult();
    }
    
    public function findMembersUpper($groupId, $mask) {
        $qb = $this->createQueryBuilder('mp');
        return $qb->innerJoin('mp.group', 'g', Join::WITH, $qb->expr()->eq('g.id', (int) $groupId))
                 ->addSelect('g')
                 ->leftJoin('mp.member', 'm')
                 ->addSelect('m')
                 ->where('BIT_AND(mp.masks, :mask) = :mask')
                 ->setParameter('mask', $mask)
                 ->orderBy('mp.masks', 'ASC')
                 ->getQuery()
                 ->getResult();
    }
    
    public function findMembersLower($groupId, $mask) {
        $qb = $this->createQueryBuilder('mp');
        return $qb->innerJoin('mp.group', 'g', Join::WITH, $qb->expr()->eq('g.id', (int) $groupId))
        ->addSelect('g')
        ->leftJoin('mp.member', 'm')
        ->addSelect('m')
        ->where('mp.masks <= :mask')
        ->setParameter('mask', $mask)
        ->orderBy('mp.masks', 'ASC')
        ->getQuery()
        ->getResult();
    }
    
    public function checkPrivilege($userId, $groupId, $mask = null) {
        $qb =  $this->createQueryBuilder('mp');
        $qb->innerJoin('mp.group', 'gr', Join::WITH, $qb->expr()->in('gr.id', (int) $groupId))
           ->addSelect('gr')
           ->innerJoin('mp.member', 'm', Join::WITH, $qb->expr()->in('m.id', (int) $userId))
           ->addSelect('m');
        if(!empty($mask)) {
            $qb->where('BIT_AND(mp.masks, :mask) = :mask')
               ->setParameter('mask', $mask);
        }
                  
        return $qb->getQuery()
                  ->getOneOrNullResult();
                
    }
    
    public function countPrivileges($id, $mask) {
        $qb =  $this->createQueryBuilder('mp');
        $qb->select('COUNT(mp.masks)')
        ->where('mp.member = :id')
        ->setParameter('id', $id, 'integer')
        ->andWhere('BIT_AND(mp.masks, :mask) = :mask')
        ->setParameter('mask', $mask);
     
        return $qb->getQuery()->getSingleScalarResult();
    }
    
    public function queryBuilderForGroups($userId, $mask, $groupId = null, $vacancies = null) {
        $qb = $this->createQueryBuilder('mp');
        $qb
        ->innerJoin('mp.group', 'g')
        ->addSelect('g')
        ->innerJoin('mp.member', 'm', Join::WITH, $qb->expr()->in('m.id', (int) $userId))
        ->where('BIT_AND(mp.masks, :mask) = :mask')
        ->setParameter('mask', $mask);
        if(!empty($groupId)) {
            $qb->andWhere('g.id <> :group')
               ->setParameter('group', $groupId);
        }
        if(!empty($vacancies))
            $qb->andWhere('g.vacancies > 0');
        return $qb;
    }
    
    public function findOneWithAll($user, $group) {
        $qb = $this->createQueryBuilder('mp');
        return $qb->innerJoin('mp.member', 'm', Join::WITH, $qb->expr()->eq('m.id', ':user'))
        ->addSelect('m')
        ->setParameter('user', $user)
        ->innerJoin('mp.group', 'gr', Join::WITH, $qb->expr()->eq('gr.id', ':group'))
        ->addSelect('gr')
        ->setParameter('group', $group)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getBlockersIds($id) {
        $result = $this->_em->createQuery('SELECT g.id FROM LEFGroupBundle:MemberPrivilege mp INNER JOIN mp.member m'
            . ' WITH m = :id LEFT JOIN mp.group g WHERE mp.masks = 0')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
            return array_map(function($data) {
                return $data['id'];
            }, $result);
    }
    
    public function getBlockedIds(array $groupIds) {
        $result = $this->_em->createQuery('SELECT g.id AS group_id, m.id AS user_id FROM LEFGroupBundle:MemberPrivilege'
            . ' mp LEFT JOIN mp.member m INNER JOIN mp.group g WITH g.id IN(:ids) WHERE mp.masks = 0')
            ->setParameter('ids', $groupIds)
            ->getScalarResult();
            return $result;array_map(function($data) {
                return $data['id'];
            }, $result);
    }
    
    public function isBlocked($groupId, $userId) {
        $qb =  $this->createQueryBuilder('mp.masks');
        $qb->innerJoin('mp.group', 'gr', Join::WITH, $qb->expr()->in('gr.id', (int) $groupId))
        ->addSelect('gr')
        ->innerJoin('mp.member', 'm', Join::WITH, $qb->expr()->in('m.id', (int) $userId))
        ->addSelect('m')
        ->where('mp.masks = 0');
        
        return !empty($qb->getQuery()->getScalarResult());
        
    }
    
    public function getGroupIds($userId, $mask) {
        $result = $this->_em->createQuery('SELECT g.id FROM LEFGroupBundle:MemberPrivilege mp INNER JOIN mp.member m'
            . ' WITH m = :id LEFT JOIN mp.group g WHERE mp.masks >= :mask')
            ->setParameter('id', $userId, 'integer')
            ->setParameter('mask', $mask, 'integer')
            ->getScalarResult();
            return array_map(function($data) {
                return $data['id'];
            }, $result);
    }
    
    public function findWithGroup($userId, $mask, $notGroups = array(), $vacancies = null) {
        $qb = $this->createQueryBuilder('mp');
        if(!empty($groupId)) {
            $qb->innerJoin('mp.group', 'g', Join::WITH, $qb->expr()->notIn('g.id', ':groups'))
               ->addSelect('g')
               ->setParameter('groups', $groups);
        } else {
            $qb->innerJoin('mp.group', 'g')
               ->addSelect('g');
        }
        $qb
        ->leftJoin('g.logo', 'l')
        ->addSelect('l')
        ->innerJoin('mp.member', 'm', Join::WITH, $qb->expr()->eq('m.id', (int) $userId))
        ->where('BIT_AND(mp.masks, :mask) = :mask')
        ->setParameter('mask', $mask);
        if(!empty($vacancies))
            $qb->andWhere('g.vacancies > 0');
        return $qb->getQuery()->getResult();
    }
}

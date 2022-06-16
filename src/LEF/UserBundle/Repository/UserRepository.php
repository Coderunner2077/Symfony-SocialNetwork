<?php
// src/LEF/UserBundle/Repository/UserRepository.php

namespace LEF\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr; 

class UserRepository extends EntityRepository {
    public function findOneWithAll($id) {
        return $this->createQueryBuilder('u')
        ->leftJoin('u.avatar', 'av')
        ->addSelect('av')
        ->where('u.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getFollowedIds($id) {
        $result = $this->_em
            ->createQuery('SELECT foll.id FROM LEFUserBundle:User u LEFT JOIN u.followed foll '
            . 'WHERE u.id = :id')
            ->setParameter('id', (int) $id)
            ->getScalarResult();
        
        return array_map(function($data) {
            return $data['id'];
        }, $result);
    }
    
    public function findOneWithFollowed($id) {
        return $this->createQueryBuilder('u')
        ->leftJoin('u.followed', 'folls')
        ->addSelect('folls')
        ->where('u.id = :id')
        ->setParameter('id', (int) $id)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function isGroupFollowed($userId, $groupId) {
        $res = $this->_em->createQuery('SELECT u.id FROM LEFUserBundle:User u INNER JOIN u.followedGroups gr'
                        . ' WITH gr.id = :group WHERE u.id = :user')
                      ->setParameters(new ArrayCollection(array(
                          new Parameter('user', $userId, 'integer'),
                          new Parameter('group', $groupId, 'integer')
                      )))
                      ->getScalarResult();
        return count($res) > 0;
    }
    
    public function isUserFollowed($followerId, $followedId) {
        $res = $this->_em->createQuery('SELECT u.id FROM LEFUserBundle:User u INNER JOIN u.followed foll'
            . ' WITH foll.id = :fld WHERE u.id = :flr')
            ->setParameters(new ArrayCollection(array(
                new Parameter('flr', $followerId, 'integer'),
                new Parameter('fld', $followedId, 'integer')
            )))
            ->getScalarResult();
        return count($res) > 0;
    }
    
    
    public function getFollowedGroupIds($id) {
        $result = $this->_em
        ->createQuery('SELECT g.id FROM LEFUserBundle:User u LEFT JOIN u.followedGroups g '
            . 'WHERE u.id = :id')
            ->setParameter('id', (int) $id)
            ->getScalarResult();
            return array_map(function($data) {
                return $data['id'];
            }, $result);
    }
    
    public function findOneWithFollowedGroups($id, array $groupIds = array()) {
        $qb = $this->createQueryBuilder('u');
        if(!empty($groupIds))
            $qb->leftJoin('u.followedGroups', 'gr', Join::WITH, $qb->expr()->notIn('gr.id', $groupIds));
        else 
            $qb->leftJoin('u.followedGroups', 'gr');
        $qb
        ->addSelect('gr')
        ->leftJoin('gr.groupCategory', 'gc')
        ->addSelect('gc')
        ->leftJoin('gr.logo', 'lg')
        ->addSelect('lg')
        ->where('u.id = :id')
        ->setParameter('id', $id);
            
        return $qb->getQuery()->getSingleResult();
    }
    
    public function findGroupFollowers($id) {
        $qb = $this->createQueryBuilder('u');
        $qb->innerJoin('u.followedGroups', 'gr', Join::WITH, $qb->expr()->eq('gr.id', (int)$id))
           ->leftJoin('u.avatar', 'avi')
           ->addSelect('avi');
        return $qb->getQuery()->getResult();
    }
    
    public function groupFollowersIterator(array $ids) {
        return $this->_em->createQuery('SELECT u FROM LEFUserBundle:User u WHERE u.id IN(:ids)') 
                    ->setParameter('ids', $ids)
                    ->iterate();
    }
    
    public function getFollowersIds($id) {
        $result = $this->_em->createQuery('SELECT u.id FROM LEFUserBundle:User u INNER JOIN u.followed fld'
            . ' WITH fld.id = :id')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
            return array_map(function($data) {
                return $data['id'];
            }, $result);
    }
    
    public function getGroupFollowersIds($id) {
        $result = $this->_em->createQuery('SELECT u.id FROM LEFUserBundle:User u INNER JOIN u.followedGroups'
            . ' gr WITH gr.id = :id')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
            return array_map(function($data) {
                return $data['id'];
            }, $result);
    }
    
    public function followersIterator(array $ids) {
        return $this->_em->createQuery('SELECT u FROM LEFUserBundle:User u WHERE u.id IN(:ids)')    
                    ->setParameter('ids', $ids)
                    ->iterate(); 
    }
    
    public function getBlockersIds($id) {
        $result = $this->_em->createQuery('SELECT u.id FROM LEFUserBundle:User u INNER JOIN u.blocked bkd'
            . ' WITH bkd.id = :id')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
        return array_map(function($data) {
            return $data['id'];
        }, $result);
    }
    
    public function getBlockedIds($id) {
        $result = $this->_em->createQuery('SELECT bkd.id FROM LEFUserBundle:User u INNER JOIN u.blocked bkd'
            . ' WHERE u.id = :id')
            ->setParameter('id', $id, 'integer')
            ->getScalarResult();
        return array_map(function($data) {
            return $data['id'];
        }, $result);
    }
    
    public function isBlocked($blocker, $blocked) {
        $result = $this->_em->createQuery('SELECT u.id FROM LEFUserBundle:User u INNER JOIN u.blocked bkd'
            . ' WITH bkd.id = :bkd WHERE u.id = :bkr')
            ->setParameters(new ArrayCollection(array(
                new Parameter('bkr', $blocker, 'integer'),
                new Parameter('bkd', $blocked, 'integer')
            )))
            ->getScalarResult();
        return count($result) > 0;
    }
    
    public function findUsers(array $ids) {
        return $this->createQueryBuilder('u')
        ->where('u.id IN(:ids)')
        ->setParameter('ids', $ids)
        ->getQuery()
        ->getResult();
    }
    
    public function searchUsers($pattern, array $orderBy = array(), $limit = null, $offset = null, $isUsername) {
        $qb =  $this->createQueryBuilder('u');
        $qb
        ->leftJoin('u.avatar', 'av')
        ->addSelect('av');
        
        $params = [];
        if($isUsername)
            $qb->where('REGEXP(u.username, :pattern) = true');
        else
            $qb->where('REGEXP(u.fullname, :pattern) = true OR REGEXP(u.username, :pattern2) = true');
        $params[] = new Parameter('pattern', '^' .$pattern . '|[[:space:]]'. $pattern);
        if($isUsername !== true)
            $params[] = new Parameter('pattern2', '^@' . $pattern);
        
        $qb->setParameters(new ArrayCollection($params));
        $i = 0;
        foreach($orderBy as $sort => $order)
            if($i++ < 1) $qb->orderBy('u.' .$sort, $order);
            else $qb->addOrderBy('u.'.$sort, $order);
            
        if(!empty($limit)) {
            $qb->setMaxResults((int) $limit);
            if(!empty($offset))
                $qb->setFirstResult((int) $offset);
            $query = $qb->getQuery();
            $paginator = new Paginator($query, $fetchJoinCollection = true);
        }
        return empty($limit) ? $qb->getQuery()->getResult() : $paginator->getIterator()->getArrayCopy();
    }
    
    public function countSearchResult($pattern, $isUsername) {
        $qb =  $this->createQueryBuilder('u');
        $qb->select('COUNT(u)');
        $params = [];
        //$qb->where($qb->expr()->like('p.content', ':hashtag'));
        if($isUsername)
            $qb->where('REGEXP(u.username, :pattern) = true');
        else 
            $qb->where('REGEXP(u.fullname, :pattern) = true OR REGEXP(u.username, :pattern2) = true');
        $params[] = new Parameter('pattern', '^' .$pattern . '|[[:space:]]' . $pattern);
        if(!$isUsername)
            $params[] = new Parameter('pattern2', '^@' . $pattern);
        $qb->setParameters(new ArrayCollection($params));
        
        return $qb->getQuery()->getSingleScalarResult();
    }
}
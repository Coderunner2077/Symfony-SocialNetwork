<?php
// src/LEF/GroupBundle/Repository/GroupEventRepository.php

namespace LEF\GroupBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr; 

class GroupEventRepository extends EntityRepository {
    public function findNextOne($groupId) {
        $qb = $this->createQueryBuilder('ge');
        return $qb->where('ge.group = :group')
                  ->andWhere('ge.electionAt >= :now')
                  ->setParameters(new ArrayCollection(array(
                      new Parameter('group', $groupId, 'integer'),
                      new Parameter('now', new \DateTime(), 'datetime')
                  )))
                  ->getQuery()
                  ->getOneOrNullResult();
    }
}
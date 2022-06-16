<?php
// src/LEF/ArticleBundle/Repository/CommentRepository.php

namespace LEF\ArticleBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CommentRepository extends NestedTreeRepository {
    public function findOneWithAll($id) {
        $qb = $this->createQueryBuilder('c');
        return $qb
        ->leftJoin('c.author', 'au')
        ->addSelect('au')
        ->leftJoin('c.article', 'ar')
        ->addSelect('ar')
        ->leftJoin('ar.group', 'gr')
        ->addSelect('gr')
        ->leftJoin('c.children', 'ch')
        ->addSelect('ch')
        ->leftJoin('ch.author', 'chau')
        ->addSelect('chau')
        ->leftJoin('c.parent', 'pt')
        ->addSelect('pt')
        ->leftJoin('pt.author', 'ptau')
        ->addSelect('ptau')
        ->where('c.id = :id')
        ->setParameter('id', (int) $id)
        ->orderBy('ch.likes', 'DESC')
        ->getQuery()
        ->getOneOrNullResult();
    }
}
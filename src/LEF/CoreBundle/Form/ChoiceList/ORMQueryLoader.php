<?php
// src/LEF/CoreBundle/Form/ChoiceList/ORMQueryLoader.php

namespace LEF\CoreBundle\Form\ChoiceList;

use Doctrine\ORM\Query;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

class ORMQueryLoader implements EntityLoaderInterface {
    private $query;
    
    public function __construct(Query $query) {
        $this->query = $query;
    }
    
    public function getEntities() {
        return $this->query->getResult();
    }
    
    public function getEntitiesByIds($identifier, array $values) {
        return $this->getEntities();
    }
}
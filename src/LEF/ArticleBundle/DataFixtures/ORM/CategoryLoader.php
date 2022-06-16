<?php
// src/LEF/ArticleBundle/DataFixtures/ORM/CategoryLoader.php

namespace LEF\ArticleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use LEF\ArticleBundle\Entity\Category;
use LEF\ArticleBundle\AttributeNamer\CategoryNamer;
use Symfony\Component\Translation\TranslatorInterface;

class CategoryLoader implements FixtureInterface {
    protected $names;
    
    public function load(ObjectManager $manager) {
        $this->names =  array(
            '11' => 'international',
            '12' => 'politics',
            '13' => 'society',
            '14' => 'economy',
            '15' => 'culture',
            '16' => 'editorial',
            '17' => 'sport',
            '18' => 'science',
            '19' => 'health',
            '20' => 'tech'
        );
        
        $names = $this->names;
        
        foreach($names as $name) {
            $category = new Category(['name' => $name]);
            $manager->persist($category);
        }
        
        $manager->flush();
    }
}


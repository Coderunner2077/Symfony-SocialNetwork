<?php
// src/LEF/GroupBundle/DataFixtures/ORM/GroupCategoryLoader.php

namespace LEF\GroupBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use LEF\GroupBundle\Entity\GroupCategory;
use Doctrine\Common\Persistence\ObjectManager;
use LEF\GroupBundle\AttributeNamer\GroupCategoryNamer;

class GroupCategoryLoader implements FixtureInterface {
    
    public function load(ObjectManager $manager) {
        $names =  array(
            '1' => 'org.media',
            '2' => 'org.profit',
            '3' => 'org.social',
            '4' => 'org.cultural',
            '5' => 'org.party',
            '6' => 'org.nonprofit'
        );
        
        foreach($names as $name) {
            $groupCategory = new GroupCategory(['name' => $name]);
            $manager->persist($groupCategory);
        }
        $manager->flush();
    }
} 
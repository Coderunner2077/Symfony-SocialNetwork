<?php
// LEF/UserBundle/DataFixtures/ORM/UserLoader.php

namespace LEF\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LEF\UserBundle\Entity\User;

class UserLoader implements FixtureInterface {
    public function load(ObjectManager $manager) {
        $names = ['Citizen', 'Test1', 'Test2'];
        $lastNames = ['Co', 'Nom1', 'Nom2'];
        $i = 0; $j = 0;
        foreach($names as $name) {
            $user = new User();
            $user->setUsername($name);
            $user->setRoles(array($name == 'Citizen' ? 'ROLE_ADMIN' : 'ROLE_AUTEUR'));
           
            $user->setFullname($name . ' ' . $lastNames[$i]);
            if($i > 0) {
                $user->setEmail(lcfirst($name). '@live.fr');
                $user->setPassword(password_hash(lcfirst($name).'_00' .++$j, PASSWORD_BCRYPT, array('cost' => 13)));
            } else {
                $user->setEmail('contact@sparknap.com');
                $user->setPassword(password_hash(lcfirst($name). '_00' .++$j, PASSWORD_BCRYPT, array('cost' => 13)));
            }
            $user->setEnabled(true);
            $manager->persist($user);
            
            $i++;
        }
        
        $manager->flush();
    }
}
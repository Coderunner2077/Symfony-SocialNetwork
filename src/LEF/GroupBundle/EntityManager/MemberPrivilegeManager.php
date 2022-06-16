<?php
// src/LEF/GroupBundle/EntityManager/MemberPrivilegeManager.php

namespace LEF\GroupBundle\EntityManager;

use Doctrine\Common\Persistence\ObjectManager;
use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Entity\MemberPrivilege;
use Symfony\Component\Security\Core\User\UserInterface;

class MemberPrivilegeManager {
    protected $manager;
    
    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }
    
    public function persist(UserInterface $user, $mask, Group $group, $flush = false) {
        $privilege = new MemberPrivilege(['group' => $group, 'member' => $user, 'masks' => $mask]);
        
        $this->manager->persist($privilege);
        if($flush)
            $this->manager->flush();
        return $privilege;
    }
    
    public function update(UserInterface $user, $mask, Group $group) {
        $this->persist($group, $user, $mask);
    }
}
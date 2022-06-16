<?php
// src/LEF/GroupBundle/Component/Vacancy/VacancyManager.php

namespace LEF\GroupBundle\Component\Vacancy;

use LEF\GroupBundle\Bitmask\BitmaskTranslator;
use LEF\GroupBundle\Session\GroupSession;
use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks; 
use LEF\GroupBundle\Entity\MemberPrivilege;

class VacancyManager {   
    protected $bitmaskTranslator,
              $groupSession,
              $choices;
    
    public function __construct(BitmaskTranslator $bitmaskTranslator, GroupSession $groupSession) {
        $this->bitmaskTranslator = $bitmaskTranslator;
        $this->groupSession = $groupSession;
    }
    
    public function removeVacancy($vacancies, $role) {
        return $vacancies & (~$this->bitmaskTranslator->roleToAction($role));
    }
    
    public function provideChoices($vacancies = null, Group $group = null, MemberPrivilege $privilege = null) {
        $this->choices = [];
        if(empty($vacancies))
            $vacancies = PrivilegeBitmasks::ADMIN;
        if(!empty($group))
            $privilege = $this->groupSession->getPrivilege($group->getId());
        if(!empty($privilege))
            $vacancies = $vacancies & (~$this->bitmaskTranslator->roleToAction($privilege->getMasks()));
        $this->processMask($vacancies, PrivilegeBitmasks::POST);
        $this->processMask($vacancies, PrivilegeBitmasks::CREATE);
        $this->processMask($vacancies, PrivilegeBitmasks::EDIT);
        $this->processMask($vacancies, PrivilegeBitmasks::DELETE);
        $this->processMask($vacancies, PrivilegeBitmasks::BLOCK);
        $this->processMask($vacancies, PrivilegeBitmasks::HIRE);
        $this->processMask($vacancies, PrivilegeBitmasks::FIRE);
        $this->processMask($vacancies, PrivilegeBitmasks::GRANT);
        return $this->choices;
    }
    
    public function processMask($vacancies, $mask) {
        if(($vacancies & $mask) === $mask) {
            $role = $this->bitmaskTranslator->actionToRole($mask);
            $this->choices[$this->bitmaskTranslator->translateMask($role)] = $mask; 
        }
    }
    
    public function translateMask($mask) {
        return $this->bitmaskTranslator->translateMask($mask);
    }
}
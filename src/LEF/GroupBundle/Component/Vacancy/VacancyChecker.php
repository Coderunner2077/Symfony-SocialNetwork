<?php
// src/LEF/GroupBundle/Component/VacancyChecker.php

namespace LEF\GroupBundle\Component\Vacancy;

use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use LEF\GroupBundle\Session\GroupSession;

class VacancyChecker {
    protected $groupSession;
    
    public function __construct(GroupSession $groupSession) {
        $this->groupSession = $groupSession;
    }
    
    public function hasVacancy(Group $group) {
        if($group->getVacancies() < PrivilegeBitmasks::MEMBER)
            return false;
        $privilege = $this->groupSession->getPrivilege($group->getId());
        if(empty($privilege))
            return true;
        return $group->getVacancies() > $privilege->getMasks();
    }
}
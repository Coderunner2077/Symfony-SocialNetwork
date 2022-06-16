<?php
// src/LEF/GroupBundle/Twig/AuthorizationCheckerExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Component\Vacancy\VacancyChecker;
use LEF\GroupBundle\Entity\Group;

class VacancyCheckerExtension extends \Twig_Extension {
    protected $vacancyChecker;
    
    public function __construct(VacancyChecker $vacancyChecker) {
        $this->vacancyChecker = $vacancyChecker;
    }
    
    public function hasVacancy(Group $group) {
        return $this->vacancyChecker->hasVacancy($group);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('has_vacancy', [$this, 'hasVacancy'])
        );
    }
}
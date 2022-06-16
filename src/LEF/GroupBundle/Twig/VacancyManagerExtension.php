<?php
// src/LEF/GroupBundle/Twig/AuthorizationManagerExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Component\Vacancy\VacancyManager;
use LEF\GroupBundle\Entity\Group;

class VacancyManagerExtension extends \Twig_Extension {
    protected $vacancyManager;
    
    public function __construct(VacancyManager $vacancyManager) {
        $this->vacancyManager = $vacancyManager;
    }
    
    public function showVacancies($masks, Group $group = null) {
        return $this->vacancyManager->provideChoices($masks, $group);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('show_vacancies', [$this, 'showVacancies'])
        );
    }
}
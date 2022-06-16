<?php
// src/LEF/CoreBundle/Twig/TimeShowerExtension.php

namespace LEF\CoreBundle\Twig;

use LEF\CoreBundle\Component\Time\TimeShower;  

class TimeShowerExtension extends \Twig_Extension {
    protected $timeShower;
    
    public function __construct(TimeShower $timeShower) {
        $this->timeShower = $timeShower;
    }
    
    public function timeAgo(\DateTime $date = null) {
        return $this->timeShower->timeAgo($date);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('timeAgo', [$this, 'timeAgo'])
        );
    }
}
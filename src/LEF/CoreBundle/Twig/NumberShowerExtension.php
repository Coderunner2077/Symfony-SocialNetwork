<?php
// src/LEF/CoreBundle/Twig/NumberShowerExtension.php

namespace LEF\CoreBundle\Twig;

use LEF\CoreBundle\Component\Number\NumberShower;

class NumberShowerExtension extends \Twig_Extension {
    protected $numberShower;
    
    public function __construct(NumberShower $numberShower) {
        $this->numberShower = $numberShower;
    }
    
    public function showNumber($number) {
        return $this->numberShower->showNumber($number);
    }
    
    public function showUnit($number) {
        return $this->numberShower->showUnit($number);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('showNumber', [$this, 'showNumber']),
            new \Twig_SimpleFunction('showUnit', [$this, 'showUnit'])
        );
    }
}
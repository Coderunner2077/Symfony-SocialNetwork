<?php
// src/LEF/CoreBundle/Component/Number/NumberShower.php

namespace LEF\CoreBundle\Component\Number;

class NumberShower {        
    public function showNumber($number) {
        if($number <= 1000)
            return $number;
        if($number < 1000000) 
            return (int) ($number / 1000) . ' K';
        return $number / 1000000 . ' M';
    }
    
    public function showUnit($number) {
        if($number < 10)
            return $number;
        
        return '9+';
    }
}
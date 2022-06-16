<?php
// src/LEF/CoreBunle/Component/Search/Object/Search.php

namespace LEF\CoreBundle\Component\Search\Object;

use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\Validator\Constraints as Assert; 

class Search extends Entity { 
    
    protected $formatedInput; 
    
    protected $keywords;
    
    protected $days;
    
    
    
    public function setInput($input) {
        $this->input = $input;
        
        return $this;
    }
    
    public function getInput() { return $this->input; }
    
    public function setKeywords(array $keywords) {
        $this->keywords = $keywords;
        
        return $this;
    }
    
    public function getKeywords() { return $this->keywords; }   
    
    public function setDays($days) {
        $this->days = $days;
        
        return $this;
    }
    
    public function getDays() { return $this->days; }
    
    public function setFormatedInput($input) {
        $this->formatedInput = $input;
        
        return $this;
    }
    
    public function getFormatedInput() { return $this->formatedInput; }
}
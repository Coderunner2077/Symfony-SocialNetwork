<?php
// src/LEF/CoreBunle/Component/Search/Object/Search.php

namespace LEF\CoreBundle\Component\Search\Object;
 
use LEF\CoreBundle\Entity\Entity; 
use Symfony\Component\Validator\Constraints as Assert; 
 
class SearchArticle extends Search implements SearchInterface {   
    /**
     * @Assert\NotBlank(message="search.notblank.article")
     * @Assert\Regex(pattern="#[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]{4,150}#i", message="search.regex.article")
     */
    protected $input;
    
    /**
     * @Assert\Range(min=0, max=365)
     */
    protected $days = 0;
    
    protected $mostLiked = false;
    
    public function setDays($days) {
        $this->days = $days;
        
        return $this;
    }
    
    public function getDays() { return $this->days; }
    
    public function setMostLiked($bool) {
        $this->mostLiked = $bool;
        
        return $this;
    }
    
    public function getMostLiked() { return $this->mostLiked; }
    
    public function isMostLiked() { return $this->mostLiked; }
    
    public function processInput() {
        $expr = $this->input;
        $quotes = [];
        if(preg_match_all("#\"([a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ][a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\s';:_.,!?-]{2,100}[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ])\"#i",
            $expr, $quotes))
            $expr = preg_replace("#\"[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\s'_.,;:!?-]{1,100}\"#i", '', $expr);
        $words = [];
        preg_match_all("#\b([a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]{4,100})\b[\s':;_.,!?-]{0,10}#i", $expr, $words);
        
        if(!empty($quotes))
            $quotes = $quotes[1];
                
        if(!empty($words[1])) {
            $words = $words[1];
            //$quotes[] = implode(' ', $words);
            $this->keywords = array_merge($quotes, $words);
            $this->formatedInput = implode(' ', $this->keywords);
        }
        
        return $this->keywords;
    }
    
    public function searchedClass() {
        return 'LEF\ArticleBundle\Entity\Article';
    }
}
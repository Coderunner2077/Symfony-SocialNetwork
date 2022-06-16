<?php
// src/LEF/CoreBunle/Component/Search/Object/SearchPost.php

namespace LEF\CoreBundle\Component\Search\Object;
 
use LEF\CoreBundle\Entity\Entity; 
use Symfony\Component\Validator\Constraints as Assert; 
 
class SearchPost extends Search implements SearchInterface {   
    /**
     * @Assert\NotBlank(message="search.notblank.post")
     * @Assert\Length(min=3, max=100, minMessage="search.length.min", maxMessage="search.length.max")
     * @Assert\Regex(pattern="#^\#?[a-z0-9_\s-]*$#i", message="search.regex.post")
     */
    protected $input;
    
    protected $mostLiked = false;
    
    public function setMostLiked($bool) {
        $this->mostLiked = $bool;
        
        return $this;
    }
    
    public function getMostLiked() { return $this->mostLiked; }
    
    public function isMostLiked() { return $this->mostLiked; }
    
    public function processInput() {
        $expr = $this->input;
        $words = [];
        preg_match_all("#\b([a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]{1,100})\b[\s':;_.,!?-]{0,10}#i", $expr, $words);
                
        if(!empty($words[1])) {
            $words = $words[1];
            //$quotes[] = implode(' ', $words);
            $this->formatedInput = '#' . implode('', $words);
            $this->keywords = array($this->formatedInput);
        }
        
        return $this->keywords;
    }
    
    public function searchedClass() {
        return 'LEF\PostBundle\Entity\Post';
    }
}
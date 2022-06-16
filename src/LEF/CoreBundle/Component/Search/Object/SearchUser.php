<?php
// src/LEF/CoreBunle/Component/Search/Object/SearchUser.php

namespace LEF\CoreBundle\Component\Search\Object;
 
use LEF\CoreBundle\Entity\Entity; 
use Symfony\Component\Validator\Constraints as Assert; 
 
class SearchUser extends Search implements SearchInterface {   
    /**
     * @Assert\NotBlank(message="search.notblank.user")
     * @Assert\Regex(pattern="#^@?[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\.'_\s-]*$#i", message="search.regex.user")
     * @Assert\Length(min=4, max=50, minMessage="search.length.min", maxMessage="search.length.max"),
     */
    protected $input;
    
    protected $username;
    
    public function setUsername($bool) {
        $this->username = $bool;
        
        return $this;
    }
    
    public function getUsername() { return $this->username; }
    
    public function isUsername() { return $this->username; }
    
    public function processInput() {
        $expr = $this->input;
        $words = [];
        //preg_match_all("#\b([a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\.'_\s-]{1,100})\b[\s':;_.,!?-]{0,10}#i", $expr, $words);
        $this->username =  preg_match('#@#', $expr) ? true : false;
        $this->formatedInput = $this->input;
        $this->keywords = array($this->formatedInput);
        
        return $this->keywords;
    }
    
    public function searchedClass() {
        return 'LEF\UserBundle\Entity\User';
    }
}
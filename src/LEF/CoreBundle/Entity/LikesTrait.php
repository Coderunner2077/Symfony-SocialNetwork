<?php
// src/LEF/CoreBundle/Entity/LikesTrait.php

namespace LEF\CoreBundle\Entity;

trait LikesTrait {
    public function setLikes($likes) {
        $this->likes = (int) $likes;
        
        return $this;
    }
    
    public function getLikes() { return $this->likes; }
    
    public function setDislikes($dislikes) {
        $this->dislikes = (int) $dislikes;
        
        return $this;
    }
    
    public function getDislikes() { return $this->dislikes; }
    
    public function incrementLikes() {
        $this->likes++;
    }
    
    public function decrementLikes() {
        $this->likes--;
    }
    
    public function incrementDislikes() {
        $this->dislikes++;
    }
    
    public function decrementDislikes() {
        $this->dislikes--;
    }
}
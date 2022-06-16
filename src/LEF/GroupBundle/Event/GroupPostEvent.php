<?php
// src/LEF/GroupBundle/Event/GroupPostEvent.php

namespace LEF\GroupBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LEF\GroupBundle\Entity\GroupPost;

class GroupPostEvent extends Event {
    protected $post;
    
    public function __construct(GroupPost $post) {
        $this->post = $post;
    }
    
    public function getPost() {
        return $this->post;
    }
    
    public function setPost(GroupPost $post) {
        $this->post = $post;
    }    
}
<?php
// src/LEF/PostBundle/Event/PostEvent.php

namespace LEF\PostBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LEF\PostBundle\Entity\Post;

class PostEvent extends Event {
    protected $post;
    
    public function __construct(Post $post) {
        $this->post = $post;
    }
    
    public function getPost() {
        return $this->post;
    }
    
    public function setPost(Post $post) {
        $this->post = $post;
    }    
}
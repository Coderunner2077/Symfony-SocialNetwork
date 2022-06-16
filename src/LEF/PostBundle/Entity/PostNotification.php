<?php
// src/LEF/PostBundle/Entity/PostNotification.php

namespace LEF\PostBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Notification;

/**
 * @ORM\Table(name="post_notification")
 * @ORM\Entity(repositoryClass="LEF\PostBundle\Repository\PostNotificationRepository")
 */
class PostNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="LEF\PostBundle\Entity\Post")
     * @ORM\JoinColumn(name="ppost_id", onDelete="CASCADE") 
     */
    protected $post;
    
    public function setPost(Post $post) {
        $this->post = $post;
        return $this;
    }
    
    public function getPost() { return $this->post; }
}
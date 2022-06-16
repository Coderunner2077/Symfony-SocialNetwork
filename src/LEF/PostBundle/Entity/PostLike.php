<?php
// src/LEF/PostBundle/Entity/PostLike.php

namespace LEF\PostBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;
  
/**
 * @ORM\Table(name="post_like")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class PostLike extends Entity {    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\PostBundle\Entity\Post", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $post;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $user;

    /**
     * Set post
     *
     * @param \LEF\PostBundle\Entity\Post $post
     *
     * @return PostLike
     */
    public function setPost(\LEF\PostBundle\Entity\Post $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \LEF\PostBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set user
     *
     * @param \LEF\UserBundle\Entity\User $user
     *
     * @return PostLike
     */
    public function setUser(\LEF\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function incrementLikes() {
        $this->getPost()->incrementLikes();
    }
    
    /**
     * @ORM\PreRemove
     */
    public function decrementLikes() {
        $this->getPost()->decrementLikes();
    }
}

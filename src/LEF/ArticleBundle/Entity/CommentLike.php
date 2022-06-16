<?php
// src/LEF/ArticleBundle/Entity/LikedComment.php

namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;
  
/**
 * @ORM\Table(name="comment_like")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CommentLike extends Entity {    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Comment", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $comment;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $user;


    /**
     * Set comment
     *
     * @param \LEF\ArticleBundle\Entity\Comment $comment
     *
     * @return LikedComment
     */
    public function setComment(\LEF\ArticleBundle\Entity\Comment $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \LEF\ArticleBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set user
     *
     * @param \LEF\UserBundle\Entity\User $user
     *
     * @return LikedComment
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
        $this->getComment()->incrementLikes();
    }
    
    /**
     * @ORM\PreRemove
     */
    public function decrementLikes() {
        $this->getComment()->decrementLikes();
    }
}

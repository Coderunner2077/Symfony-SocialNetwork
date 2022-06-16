<?php
// src/LEF/GroupBundle/Entity/GroupPostLike.php

namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;
  
/**
 * @ORM\Table(name="group_post_like")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 * @ORM\HasLifeCycleCallbacks
 */
class GroupPostLike extends Entity {    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\GroupPost", cascade={"persist"})
     * @ORM\JoinColumn(name="group_post_id", nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $groupPost;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $user;

    /**
     * Set groupPost
     *
     * @param \LEF\GroupBundle\Entity\GroupPost $groupPost
     *
     * @return GroupPostLike
     */
    public function setGroupPost(\LEF\GroupBundle\Entity\GroupPost $groupPost)
    {
        $this->groupPost = $groupPost;

        return $this;
    }

    /**
     * Get groupPost
     *
     * @return \LEF\GroupBundle\Entity\GroupPost
     */
    public function getGroupPost()
    {
        return $this->groupPost;
    }

    /**
     * Set user
     *
     * @param \LEF\UserBundle\Entity\User $user
     *
     * @return GroupPostLike
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
        $this->getGroupPost()->incrementLikes();
    }
    
    /**
     * @ORM\PreRemove
     */
    public function decrementLikes() {
        $this->getGroupPost()->decrementLikes();
    }
}

<?php
// src/LEF/GroupBundle/Entity/GroupPostNotification.php

namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Notification;

/**
 * @ORM\Table(name="group_post_notification")
 * @ORM\Entity(repositoryClass="LEF\GroupBundle\Repository\GroupPostNotificationRepository")
 */
class GroupPostNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\GroupPost")
     * @ORM\JoinColumn(name="group_post_id", onDelete="CASCADE") 
     */
    protected $groupPost;
    
    public function setGroupPost(GroupPost $post) {
        $this->groupPost = $post;
        return $this;
    }
    
    public function getGroupPost() { return $this->groupPost; }
}
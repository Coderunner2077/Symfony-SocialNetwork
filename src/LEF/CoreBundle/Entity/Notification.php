<?php
// src/LEF/CoreBundle/Entity/Notification.php

namespace LEF\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\UserBundle\Entity\User;

/**
 * @ORM\MappedSuperclass
 */
class Notification extends Entity {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;
    
    /**
     * @ORM\Column(name="is_viewed", type="boolean", options={"default": false})
     */
    protected $viewed = false;
    
    public function setViewed($viewed) {
        $this->viewed = $viewed;
    }
    
    public function getViewed() { return $this->viewed; }
    
    public function isViewed() { return $this->viewed; }    
    
    public function  setUser(User $user) {
        $this->user = $user;
        return $this;
    }
    
    public function getUser() { return $this->user; }
    
    public function getId() { return $this->id; }
}
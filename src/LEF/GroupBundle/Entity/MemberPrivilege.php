<?php
// src/LEF/GroupBundle/Entity/MemberPrivilege.php

namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;

/**
 * @ORM\Table(name="member_privilege")
 * @ORM\Entity(repositoryClass="LEF\GroupBundle\Repository\MemberPrivilegeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MemberPrivilege extends Entity implements MemberPrivilegeInterface {    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="member_id", onDelete="CASCADE")
     */
    protected $member;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\Group", cascade={"persist"}, inversedBy="privileges")
     * @ORM\JoinColumn(name="group_id", onDelete="CASCADE")

     */
    protected $group;
    
    /**
     * @ORM\Column(name="masks", type="integer")
     */
    protected $masks;
    
    /**
     * Set member
     *
     * @param \LEF\UserBundle\Entity\User $member
     *
     * @return MemberPrivilege
     */
    public function setMember(\LEF\UserBundle\Entity\User $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getMember()
    {
        return $this->member;
    }

   
    public function setMasks($masks)
    {
        $this->masks = $masks;

        return $this;
    }

    public function getMasks()
    {
        return $this->masks;
    }

    /**
     * Set group
     *
     * @param \LEF\UserBundle\Entity\Group $group
     *
     * @return MemberPrivilege
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
        $group->addPrivilege($this);

        return $this;
    }
    
    public function isGranted($mask) {
        return ($this->masks & $mask) === $mask;
    }
    
    public function grantPrivilege($mask) {
        $this->masks |= $mask;
    }
    
    public function removePrivilege($mask) {
        $this->masks = $this->masks & ~$mask;
    }

    /**
     * Get group
     *
     * @return \LEF\GroupBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * @ORM\PreRemove
     */
    public function preRemove() {
        $this->group->removePrivilege($this);
    }
}

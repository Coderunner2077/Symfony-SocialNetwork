<?php
// src/LEF/GroupBundle/Session/MemberPrivilege.php

namespace LEF\GroupBundle\Session;

use LEF\GroupBundle\Entity\MemberPrivilegeInterface;
use LEF\CoreBundle\Entity\Entity;

class MemberPrivilege extends Entity implements MemberPrivilegeInterface {
    protected $member;
    protected $group;
    protected $masks;
    protected $updatedAt;
    
    public function setMember($member)
    {
        $this->member = $member;
        
        return $this;
    }
    
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
    
    public function setGroup($group)
    {
        $this->group = $group;
        
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
    
    public function getGroup()
    {
        return $this->group;
    }
    
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
    
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
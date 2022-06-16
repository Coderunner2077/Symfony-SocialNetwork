<?php
// src/LEF/GroupBundle/Entity/MemberPrivilegeInterface.php

namespace LEF\GroupBundle\Entity;

interface MemberPrivilegeInterface {
    public function isGranted($mask); 
    
    public function grantPrivilege($mask);
    
    public function removePrivilege($mask);
}
<?php
// src/LEF/GroupBundle/Twig/GroupCategoryNamesExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\AttributeNamer\GroupCategoryNamer;

class GroupCategoryNamerExtension extends \Twig_Extension {
    protected $groupCategoryNamer;
    
    public function __construct(GroupCategoryNamer $namer) {
        $this->groupCategoryNamer = $namer;
    }
    
    public function getGroupCategoryNames() {
        return $this->groupCategoryNamer->provideNames();
    }
    
    public function getGroupCategoryName($id) {
        return $this->groupCategoryNamer->provideName($id);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('group_cats', array($this, 'getGroupCategoryNames')),
            new \Twig_SimpleFunction('group_cat', array($this, 'getGroupCategoryName'))
        );
    }
    
    public function getName() {
        return 'LefGroupCategoryNamer';
    }
}
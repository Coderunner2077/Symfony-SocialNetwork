<?php
// src/LEF/GroupBundle/Twig/BitmaskTranslatorExtension.php

namespace LEF\GroupBundle\Twig;

use LEF\GroupBundle\Bitmask\BitmaskTranslator;
use LEF\GroupBundle\Entity\MemberPrivilegeInterface;

class BitmaskTranslatorExtension extends \Twig_Extension {
    protected $bitmaskTranslator;
    
    public function __construct(BitmaskTranslator $translator) {
        $this->bitmaskTranslator = $translator;
    }
    
    public function translate(MemberPrivilegeInterface $privilege) {
        return $this->bitmaskTranslator->translate($privilege);
    }
    
    public function translateMask($mask) {
        return $this->bitmaskTranslator->translateMask($mask);
    }
    
    public function translateActions($masks, $convert = false) {
        return $this->bitmaskTranslator->translateActions($masks, $convert);
    }
    
    public function translateStatus($mask) {
        return $this->bitmaskTranslator->translateStatus($mask);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('showMember', array($this, 'translate')),
            new \Twig_SimpleFunction('show_actions', array($this, 'translateActions')),
            new \Twig_SimpleFunction('show_status', array($this, 'translateStatus')),
            new \Twig_SimpleFunction('show_mask', array($this, 'translateMask'))
        );
    }
    
    public function getName() {
        return 'LefBitmaskTranslator';
    }
}
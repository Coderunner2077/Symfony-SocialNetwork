<?php
// src/LEF/GroupBundle/Bitmask/BitmaskTranslator.php

namespace LEF\GroupBundle\Bitmask;

use Symfony\Component\Translation\TranslatorInterface;
use LEF\GroupBundle\Entity\MemberPrivilegeInterface;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use LEF\GroupBundle\Entity\Application;

class BitmaskTranslator {    
    protected $translator,
              $actions;
    
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
        $this->actionToRole = array(
            PrivilegeBitmasks::DICTATE => PrivilegeBitmasks::ADMIN,
            PrivilegeBitmasks::GRANT => PrivilegeBitmasks::MANAGER,
            PrivilegeBitmasks::FIRE => PrivilegeBitmasks::HR_MANAGER,
            PrivilegeBitmasks::HIRE => PrivilegeBitmasks::HEADHUNTER,
            PrivilegeBitmasks::BLOCK => PrivilegeBitmasks::OPERATOR,
            PrivilegeBitmasks::DELETE => PrivilegeBitmasks::MODERATOR,
            PrivilegeBitmasks::EDIT => PrivilegeBitmasks::EDITOR,
            PrivilegeBitmasks::CREATE => PrivilegeBitmasks::COLUMNIST,
            PrivilegeBitmasks::POST => PrivilegeBitmasks::MEMBER,
            PrivilegeBitmasks::COMMENT => PrivilegeBitmasks::SUBSCRIBER
        );
        $this->roles = array(
            PrivilegeBitmasks::BLOCKED => $this->translator->trans('bitmask.blocked', array(), 'attributes'),
            PrivilegeBitmasks::SUBSCRIBER => $this->translator->trans('bitmask.subscriber', array(), 'attributes'),
            PrivilegeBitmasks::MEMBER => $this->translator->trans('bitmask.member', array(), 'attributes'),
            PrivilegeBitmasks::COLUMNIST => $this->translator->trans('bitmask.columnist', array(), 'attributes'),
            PrivilegeBitmasks::EDITOR => $this->translator->trans('bitmask.editor', array(), 'attributes'),
            PrivilegeBitmasks::MODERATOR => $this->translator->trans('bitmask.moderator', array(), 'attributes'),
            PrivilegeBitmasks::OPERATOR => $this->translator->trans('bitmask.operator', array(), 'attributes'),
            PrivilegeBitmasks::HEADHUNTER => $this->translator->trans('bitmask.headhunter', array(), 'attributes'),
            //PrivilegeBitmasks::SUBMANAGER => $this->translator->trans('bitmask.submanager', array(), 'attributes'),
            PrivilegeBitmasks::HR_MANAGER => $this->translator->trans('bitmask.hr_manager', array(), 'attributes'),
            //PrivilegeBitmasks::MANAGER => $this->translator->trans('bitmask.manager', array(), 'attributes'),
            PrivilegeBitmasks::GRANTER => $this->translator->trans('bitmask.granter', array(), 'attributes'),
            PrivilegeBitmasks::MANAGER => $this->translator->trans('bitmask.manager', array(), 'attributes'),
            PrivilegeBitmasks::ADMIN => $this->translator->trans('bitmask.admin', array(), 'attributes')
        );
        
        $this->status = array(
            Application::OFFER => $this->translator->trans('status.offer', array(), 'attributes'),
            Application::DEMAND => $this->translator->trans('status.demand', array(), 'attributes'),
            Application::COUNTER_DEMAND => $this->translator->trans('status.counter_demand', array(), 'attributes'),
            Application::COUNTER_OFFER => $this->translator->trans('status.counter_offer', array(), 'attributes'),
            Application::OFFER_DECLINED => $this->translator->trans('status.offer_declined', array(), 'attributes'),
            Application::DEMAND_CANCELLED => $this->translator->trans('status.demand_cancelled', array(), 'attributes'),
            Application::CANCELLED => $this->translator->trans('status.cancelled', array(), 'attributes'),
            Application::REFUSED => $this->translator->trans('status.refused', array(), 'attributes')
        );
        
        $this->actions = array(
            PrivilegeBitmasks::VIEW => $this->translator->trans('bitmask.view', array(), 'attributes'),
            PrivilegeBitmasks::COMMENT => $this->translator->trans('bitmask.comment', array(), 'attributes'),
            PrivilegeBitmasks::POST => $this->translator->trans('bitmask.post', array(), 'attributes'),
            PrivilegeBitmasks::CREATE => $this->translator->trans('bitmask.create', array(), 'attributes'),
            PrivilegeBitmasks::EDIT => $this->translator->trans('bitmask.edit', array(), 'attributes'),
            PrivilegeBitmasks::DELETE => $this->translator->trans('bitmask.delete', array(), 'attributes'),
            PrivilegeBitmasks::BLOCK => $this->translator->trans('bitmask.block', array(), 'attributes'),
            PrivilegeBitmasks::HIRE => $this->translator->trans('bitmask.hire', array(), 'attributes'),
            PrivilegeBitmasks::FIRE => $this->translator->trans('bitmask.fire', array(), 'attributes'),
            PrivilegeBitmasks::GRANT => $this->translator->trans('bitmask.grant', array(), 'attributes'),
            PrivilegeBitmasks::DICTATE => $this->translator->trans('bitmask.dictate', array(), 'attributes')
        );
    }
    
    public function translate(MemberPrivilegeInterface $privilege) {
        return $this->translateMask($privilege->getMasks());
    }
    
    public function translateMask($masks) {
        return $this->roles[$masks]; 
    }
    
    public function reverse($masks) {
        if(isset($this->roles[$masks]))
            return $this->roles[$masks];
        
        return PrivilegeBitmasks::getConstant($masks);
    }
    
    public function translateStatus($mask) {
        return $this->status[$mask];
    }
    
    public function translateActions($masks, $convert = false) {
        $actions = [];
        if($convert === true)
            $masks = $this->actionToRole($masks);
        $this->processMask($actions, $masks, PrivilegeBitmasks::VIEW);
        $this->processMask($actions, $masks, PrivilegeBitmasks::COMMENT);
        $this->processMask($actions, $masks, PrivilegeBitmasks::POST);
        $this->processMask($actions, $masks, PrivilegeBitmasks::CREATE);
        $this->processMask($actions, $masks, PrivilegeBitmasks::EDIT);
        $this->processMask($actions, $masks, PrivilegeBitmasks::DELETE);
        $this->processMask($actions, $masks, PrivilegeBitmasks::BLOCK);
        $this->processMask($actions, $masks, PrivilegeBitmasks::HIRE);
        $this->processMask($actions, $masks, PrivilegeBitmasks::FIRE);
        $this->processMask($actions, $masks, PrivilegeBitmasks::GRANT);
        $this->processMask($actions, $masks, PrivilegeBitmasks::DICTATE);
        return $actions;
    }
    
    public function processMask(&$actions, $masks, $mask) {
        if(($masks & $mask) === $mask)
            $actions[] = $this->translateAction($mask);
    }
    public function translateAction($mask) {
        return $this->actions[$mask];
    }
    
    public function actionToRole($mask) {
        return $this->actionToRole[$mask];
    }
    
    public function roleToAction($masks) {
        return array_search($masks, $this->actionToRole);
    }
}
<?php
// src/LEF/GroupBundle/AttrubuteNamer/GroupCategoryNamer.php

namespace LEF\GroupBundle\AttributeNamer;

use Symfony\Component\Translation\TranslatorInterface;

class GroupCategoryNamer {
    protected $names,
              $trans;
    
    public function __construct(TranslatorInterface $trans) {
        $this->trans = $trans;
        $this->names =  array(
            '1' => $this->trans->trans('org.media', array(), 'attributes'),
            '2' => $this->trans->trans('org.profit', array(), 'attributes'),
            '3' => $this->trans->trans('org.social', array(), 'attributes'),
            '4' => $this->trans->trans('org.cultural', array(), 'attributes'),
            '5' => $this->trans->trans('org.party', array(), 'attributes'),
            '6' => $this->trans->trans('org.nonprofit', array(), 'attributes')
        );
    }
    
    public function provideNames() {
        return $this->names;
    }
    
    public function provideName($id) {
        return isset($this->names[$id]) ? $this->names[$id] : 'invalid_id';
    }
}
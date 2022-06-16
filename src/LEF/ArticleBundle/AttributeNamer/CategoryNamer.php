<?php
// src/LEF/ArticleBundle/AttrubuteNamer/CategoryNamer.php

namespace LEF\ArticleBundle\AttributeNamer;

use Symfony\Component\Translation\TranslatorInterface;

class CategoryNamer {
    protected $names,
              $trans;
    
    public function __construct(TranslatorInterface $trans) {
        $this->trans = $trans;
        $this->names =  array(
            '11' => $this->trans->trans('international', array(), 'attributes'),
            '12' => $this->trans->trans('politics', array(), 'attributes'),
            '13' => $this->trans->trans('society', array(), 'attributes'),
            '14' => $this->trans->trans('economy', array(), 'attributes'),
            '15' => $this->trans->trans('culture', array(), 'attributes'),
            '16' => $this->trans->trans('editorial', array(), 'attributes'),
            '17' => $this->trans->trans('sport', array(), 'attributes'),
            '18' => $this->trans->trans('science', array(), 'attributes'),
            '19' => $this->trans->trans('health', array(), 'attributes'),
            '20' => $this->trans->trans('tech', array(), 'attributes')
       );
    }
    
    public function provideNames() {
        return $this->names;
    }
    
    public function provideName($id) {
        return isset($this->names[$id]) ? $this->names[$id] : 'invalid_id';
    }
}
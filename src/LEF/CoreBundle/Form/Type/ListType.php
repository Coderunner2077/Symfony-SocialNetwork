<?php
// src/LEF/CoreBundle/Form/Type/ListType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ListType extends AbstractType {    
   public function getParent() {
       return ChoiceType::class;
   }
   
   public function getBlockPrefix() {
       return 'lef_list';
   }
}


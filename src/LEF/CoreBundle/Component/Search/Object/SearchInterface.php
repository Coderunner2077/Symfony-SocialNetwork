<?php
// src/LEF/CoreBundle/Component/Search/Object/SearchInterface.php

namespace LEF\CoreBundle\Component\Search\Object;
 
interface SearchInterface {
    public function processInput();
    public function searchedClass();
}
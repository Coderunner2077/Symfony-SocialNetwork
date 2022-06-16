<?php
// src/LEF/ArticleBundle/Twig/CategoryNamesExtension.php

namespace LEF\ArticleBundle\Twig;

use LEF\ArticleBundle\AttributeNamer\CategoryNamer;  

class CategoryNamesExtension extends \Twig_Extension {
    protected $categoryNamer;
    
    public function __construct(CategoryNamer $namer) {
        $this->categoryNamer = $namer;
    }
    
    public function getCategoryNames() {
        return $this->categoryNamer->provideNames();
    }
    
    public function getCategoryName($id) {
        return $this->categoryNamer->provideName($id);
    }
    
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('cat_names', [$this, 'getCategoryNames']),
            new \Twig_SimpleFunction('cat_name', [$this, 'getCategoryName'])
        );
    }
    
    public function getName() {
        return 'LEFCategoryNames';
    }
}
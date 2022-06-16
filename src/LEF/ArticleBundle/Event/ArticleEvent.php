<?php
// src/LEF/ArticleBundle/Event/ArticleEvent.php

namespace LEF\ArticleBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use LEF\ArticleBundle\Entity\Article;

class ArticleEvent extends Event {
    protected $article;
    
    public function __construct(Article $article) {
        $this->article = $article;
    }
    
    public function getArticle() {
        return $this->article;
    }
    
    public function setArticle(Article $article) {
        $this->article = $article;
    }    
}
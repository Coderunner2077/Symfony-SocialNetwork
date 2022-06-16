<?php
// src/LEF/ArticleBundle/Entity/ArticleNotification.php

namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Notification;

/**
 * @ORM\Table(name="article_notification")
 * @ORM\Entity(repositoryClass="LEF\ArticleBundle\Repository\ArticleNotificationRepository")
 */
class ArticleNotification extends Notification {
    /**
     * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Article")
     * @ORM\JoinColumn(name="group_post_id", onDelete="CASCADE") 
     */
    protected $article;
    
    public function setArticle(Article $article) {
        $this->article = $article;
        return $this;
    }
    
    public function getArticle() { return $this->article; }
}
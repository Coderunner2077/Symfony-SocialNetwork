<?php
// src/LEF/ArticleBundle/Entity/DislikedArticle.php

namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;
  
/**
 * @ORM\Table(name="article_dislike")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArticleDislike extends Entity {    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Article", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $article;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    protected $user;

    /**
     * Set article
     *
     * @param \LEF\ArticleBundle\Entity\Article $article
     *
     * @return ArticleScore
     */
    public function setArticle(\LEF\ArticleBundle\Entity\Article $article)  
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \LEF\ArticleBundle\Entity\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set user
     *
     * @param \LEF\UserBundle\Entity\User $user
     *
     * @return ArticleScore
     */
    public function setUser(\LEF\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function incrementDislikes() {
        $this->getArticle()->incrementDislikes();
    }
    
    /**
     * @ORM\PreRemove
     */
    public function decrementDislikes() {
        $this->getArticle()->decrementDislikes();
    }
}

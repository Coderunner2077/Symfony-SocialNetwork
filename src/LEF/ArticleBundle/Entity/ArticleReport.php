<?php
// src/LEF/ArticleBundle/Entity/ArticleReport.php

namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity;
use LEF\CoreBundle\Entity\Report;  
use LEF\CoreBundle\Component\Report\ReportTrait;

/**
 * @ORM\Table(name="article_report")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 */
class ArticleReport extends Entity implements Report {
    use ReportTrait;
    
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $reporter;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Article")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $article;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reporter
     *
     * @param \LEF\UserBundle\Entity\User $reporter
     *
     * @return ArticleReport
     */
    public function setReporter(\LEF\UserBundle\Entity\User $reporter)
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * Get reporter
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Set article
     *
     * @param \LEF\ArticleBundle\Entity\Article $article
     *
     * @return ArticleReport
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
}

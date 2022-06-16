<?php
// src/LEF/ArticleBundle/Entity/CommentReport.php

namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity;
use LEF\CoreBundle\Entity\Report;
use LEF\CoreBundle\Component\Report\ReportTrait; 

/**
 * @ORM\Table(name="comment_report")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 */
class CommentReport extends Entity implements Report {    
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
     * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Comment")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $comment;

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
     * @return ArticleComplaint
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
     * Set comment
     *
     * @param \LEF\ArticleBundle\Entity\Comment $article
     *
     * @return CommentReport
     */
    public function setComment(\LEF\ArticleBundle\Entity\Comment $comment)
    {
        $this->comment = $comment;
        
        return $this;
    }
    
    /**
     * Get article
     *
     * @return \LEF\ArticleBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}

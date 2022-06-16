<?php
// src/LEF/PostBundle/Entity/PostReport.php

namespace LEF\PostBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity;
use LEF\CoreBundle\Entity\Report;
use LEF\CoreBundle\Component\Report\ReportTrait;        
   
/**
 * @ORM\Table(name="post_report")
 * @ORM\Entity(repositoryClass="LEF\PostBundle\Repository\PostComplaintRepository")
 */
class PostReport extends Entity implements Report {
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
     * @ORM\ManyToOne(targetEntity="LEF\PostBundle\Entity\Post")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $post;

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
     * Set post
     *
     * @param \LEF\PostBundle\Entity\Post $post
     *
     * @return PostReport
     */
    public function setPost(\LEF\PostBundle\Entity\Post $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \LEF\PostBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }
}

<?php
// src/LEF/GroupBundle/Entity/GroupPostReport.php

namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity;
use LEF\CoreBundle\Entity\Report;
use LEF\CoreBundle\Component\Report\ReportTrait;  

/** 
 * @ORM\Table(name="group_post_report")
 * @ORM\Entity(repositoryClass="LEF\CoreBundle\Repository\EntityRepository")
 */
class GroupPostReport extends Entity implements Report {
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
     * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\GroupPost")
     * @ORM\JoinColumn(name="group_post_id", nullable=false, onDelete="CASCADE")
     */
    protected $groupPost;

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
     * Set bitmask
     *
     * @param integer $bitmask
     *
     * @return ArticleComplaint
     */
    public function setBitmask($bitmask)
    {
        $this->bitmask = $bitmask;

        return $this;
    }

    /**
     * Get bitmask
     *
     * @return integer
     */
    public function getBitmask()
    {
        return $this->bitmask;
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
     * Set groupPost
     *
     * @param \LEF\GroupBundle\Entity\GroupPost $groupPost
     *
     * @return GroupPostReport
     */
    public function setGroupPost(\LEF\GroupBundle\Entity\GroupPost $groupPost)
    {
        $this->groupPost = $groupPost;

        return $this;
    }

    /**
     * Get groupPost
     *
     * @return \LEF\GroupBundle\Entity\GroupPost
     */
    public function getGroupPost()
    {
        return $this->groupPost;
    }
}

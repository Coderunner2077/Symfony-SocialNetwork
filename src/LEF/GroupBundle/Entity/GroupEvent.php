<?php
namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity; 

/** 
 * @ORM\Table(name="group_event")
 * @ORM\Entity(repositoryClass="LEF\GroupBundle\Repository\GroupEventRepository")
 */
class GroupEvent extends Entity {  	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\Group", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 * @Assert\Valid()
	 * @Assert\NotBlank()
	 */
	protected $group;

	/**
	 * @ORM\Column(name="election_at", type="datetime")
	 * @Assert\DateTime()
	 */
	protected $electionAt;
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
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
	 * Set group
	 *
	 * @param \LEF\GroupBundle\Entity\Group $group
	 *
	 * @return GroupPost
	 */
	public function setGroup($group)
	{
	    if($group instanceof Group) {
	        $this->group = $group;
	        
	        return $this;
	    }
	    if($group instanceof MemberPrivilege) {
	        $this->group = $group->getGroup();
	        
	        return $this;
	    }
	}
	
	/**
	 * Get group
	 *
	 * @return \LEF\GroupBundle\Entity\Group
	 */
	public function getGroup()
	{
	    return $this->group;
	}
	
	public function setElectionAt(\DateTime $date) {
	    $this->electionAt = $date;
	    return $this;
	}
	
	public function getElectionAt() { return $this->electionAt; }
	
	public function getElectionDate() { return $this->electionAt; }
	
	/**
	 * @Assert\IsTrue(message="group_event.too_soon")
	 */
	public function isAfterMonth() {
	    if(empty($this->electionAt))
	        return false;
	    $now = new \DateTime('today');
	    if($now > $this->electionAt)
	        return true;
	    return $now->diff($this->electionAt)->days >= 30;
	}
	
	/**
	 * @Assert\IsFalse(message="group_event.wrong_date")
	 */
	public function isWrongDate() {
	    $now = new \DateTime('today');
	    return $now > $this->electionAt;
	}
	
}

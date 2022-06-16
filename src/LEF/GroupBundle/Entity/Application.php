<?php
// src/LEF/GroupBundle/Entity/ApplicantPrivilege.php

namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;

/**
 * @ORM\Table(name="application")
 * @ORM\Entity(repositoryClass="LEF\GroupBundle\Repository\ApplicationRepository")
 */
class Application extends Entity {
    const DEMAND =                    0b1;
    const OFFER =                    0b10;
    const COUNTER_DEMAND =          0b100; 
    const COUNTER_OFFER =          0b1000; // 8
    const OFFER_DECLINED =        0b10000; // 16
    const DEMAND_CANCELLED =     0b100000; // 32
    const CANCELLED =           0b1000000; // 64
    const REFUSED =            0b10000000; // 128
    const MASKS =              0b11111111; // 255
    
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="applicant_id", onDelete="CASCADE")
     */
    protected $applicant;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\Group", cascade={"persist"})
     * @ORM\JoinColumn(name="group_id", onDelete="CASCADE")
     */
    protected $group;
    
    /**
     * @ORM\Column(name="offer", type="integer", nullable=true)
     */
    protected $offer;
    
    /**
     * @ORM\Column(name="demand", type="integer", nullable=true)
     */
    protected $demand;
    
    /**
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    protected $status;
    
    /**
     * @ORM\Column(name="requested_at", type="datetime")
     */
    protected $requestedAt;
    
    protected $vacancies;
    
    protected $masks;
    
    /**
     * Set applicant
     *
     * @param \LEF\UserBundle\Entity\User $applicant
     *
     * @return ApplicantPrivilege
     */
    public function setApplicant(\LEF\UserBundle\Entity\User $applicant)
    {
        $this->applicant = $applicant;

        return $this;
    }

    /**
     * Get applicant
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getApplicant()
    {
        return $this->applicant;
    }

    /**
     * Set group
     *
     * @param \LEF\UserBundle\Entity\Group|MemberPrivilege $group
     *
     * @return Application
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
    
    public function isGranted($mask) {
        return ($this->masks & $mask) === $mask;
    }
    
    public function grantPrivilege($mask) {
        $this->masks |= $mask;
    }
    
    public function removePrivilege($mask) {
        $this->masks = $this->masks & ~$mask;
    }
    
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function prePersist() {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Set offer.
     *
     * @param int $offer
     *
     * @return Application
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;
        
        
        return $this;
    }

    /**
     * Get offer.
     *
     * @return int
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * Set demand.
     *
     * @param int $demand
     *
     * @return Application
     */
    public function setDemand($demand)
    {
        $this->demand = $demand;

        return $this;
    }

    /**
     * Get demand.
     *
     * @return int
     */
    public function getDemand()
    {
        return $this->demand;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Application
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set requestedAt.
     *
     * @param \DateTime $requestedAt
     *
     * @return Application
     */
    public function setRequestedAt($requestedAt)
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    /**
     * Get requestedAt.
     *
     * @return \DateTime
     */
    public function getRequestedAt()
    {
        return $this->requestedAt;
    }
    
    /**
     * Set vacancies.
     *
     * @param int|null $vacancies
     *
     * @return Group
     */
    public function setVacancies($vacancies)
    {
        $this->vacancies = $vacancies;
        
        return $this;
    }
    
    /**
     * Get vacancies.
     *
     * @return int|null
     */
    public function getVacancies()
    {
        return $this->vacancies;
    }
    
    public function processDemand() {
        if(empty($this->id) || empty($this->offer))
            $this->status = self::DEMAND;
        else
            $this->status = self::COUNTER_DEMAND;
    }
    
    public function processOffer() {
        if(empty($this->id) || empty($this->demand))
            $this->status = self::OFFER;
        else
            $this->status = self::COUNTER_OFFER;
    }
    
    public function canApply() {
        return $this->status !== self::REFUSED;
    }
    
    public function canInvite() {
        return $this->status !== self::OFFER_DECLINED;
    }
    
    public function hasStatus($mask) {
        return $this->status === $mask;
    }
    
    public function isOffer() {
        return !empty($this->id) && !empty($this->offer);
    }
    
    public function isDemand() {
        return !empty($this->id) && !empty($this->demand);
    }
    
    public function isInvitation() {
        return $this->status === self::OFFER || $this->status === self::COUNTER_OFFER;
    }
    
    public function isApplication() {
        return $this->status === self::DEMAND || $this->status === self::COUNTER_DEMAND;
    }
    
    public function isRefused() {
        return $this->status === self::REFUSED;
    }
    
    public function isAccepted() {
        return $this->demand == $this->offer;
    }
    
    public function isDeclined() { return $this->status === self::OFFER_DECLINED; }
    
    public function getId() { return $this->id; }
    
    public function isValid() {
        return (($this->offer & self::MASKS) | ($this->demand & self::MASKS)) > 0;
    }
}

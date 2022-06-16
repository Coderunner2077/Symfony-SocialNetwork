<?php
// src/LEF/CoreBundle/Component/Report/ReportTrait.php

namespace LEF\CoreBundle\Component\Report;

trait ReportTrait {
    /**
     * @ORM\Column(name="severity", type="integer")
     */
    protected $severity;
    
    /**
     * Set severity
     *
     * @param integer $severity
     *
     * @return ReportInterface
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        
        return $this;
    }
    
    /**
     * Get severity
     *
     * @return integer
     */
    public function getSeverity()
    {
        return $this->severity;
    }
}
<?php
// src/LEF/CoreBundle/Time/TimeShower.php

namespace LEF\CoreBundle\Component\Time;

use Symfony\Component\Translation\TranslatorInterface;

class TimeShower {
    protected $trans;
    
    public function __construct(TranslatorInterface $translator) {
        $this->trans = $translator;
    }
    
    public function diff(\DateTime $date) {
        $now = new \DateTime();
        return $now->diff($date);
    }
    
    public function convert(\DateInterval $interval) {
        if($interval->y > 0)
            return ['year' => $interval->y];
        if($interval->m) 
            return ['month' => $interval->m];
        if($interval->d)
            return ['day' => $interval->d];
        if($interval->h)
            return ['hour' => $interval->h];
        if($interval->i)
            return ['minute' => $interval->i];
        
        return ['second' => $interval->s];
    }
    
    public function translate(array $data) {
        foreach($data as $type => $unit)
            return $this->trans->transChoice('time.ago.' . $type, $unit, array(), 'attributes');
    }
    
    public function timeAgo(\DateTime $date = null) {
        return empty($date) ? 'VOILAAAAAA' : $this->translate($this->convert($this->diff($date)));
    }
}
<?php
// src/LEF/CoreBunle/Entity/Contact.php

namespace LEF\CoreBundle\Entity;

use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\Validator\Constraints as Assert; 

class Contact extends Entity {    
    protected $id;
    /**
     * @Assert\Length(min=2)
     * @Assert\NotBlank()
     */
    protected $name;
    
    /**
     * @Assert\Email
     */
    protected $email;
    
    /**
     * @Assert\Length(min=50, max=500)
     * @Assert\NotBlank()
     */
    protected $message;
    
    public function setId($id) { 
        $this->id = $id;
        return $this;
    }
    
    public function getId() { return $this->id; }
  
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    public function getName() { return $this->name; }
    
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
    public function getEmail() { return $this->email; }
    
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }
    
    public function getMessage() { return $this->message; }
    
}
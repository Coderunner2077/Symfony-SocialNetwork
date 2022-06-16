<?php
// src/LEF/GroupBundle/Entity/GroupCategory.php

namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="group_category")
 * @ORM\Entity
 */
class GroupCategory extends Entity {
    /**
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    protected $name;
    
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
     * Set name
     *
     * @param string $name
     *
     * @return GroupCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}

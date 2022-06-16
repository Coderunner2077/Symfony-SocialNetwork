<?php
// src/LEF/ArticleBundle/Entity/Category.php

namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;

/**
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="LEF\ArticleBundle\Repository\CategoryRepository")
 */
class Category extends Entity {		
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    
    /**
     * @ORM\Column(name="name", type="string", length=150, unique=true)
     */
    protected $name;	
    
    
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
     * @return Category
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

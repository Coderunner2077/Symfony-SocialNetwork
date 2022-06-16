<?php
// src/LEF/CoreBunle/Entity/Image.php

namespace LEF\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LEF\CoreBundle\Entity\Entity;
use Symfony\Component\Validator\Constraints as Assert; 
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * @ORM\Table(name="image")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Image extends Entity {
    /**
     * @ORM\Column(name="url", type="string", length=15)
     */
    protected $url;
    
    /**
     * @ORM\Column(name="alt", type="string", length=150)
     */
    protected $alt;
    
    /**
     * @Assert\Image(maxSize="2M")
     */
    protected $file;
    
    protected $tempFilename;
    
    protected $uploadDir ='uploads/img';
    
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    public function __construct(array $data = array()) {
        parent::__construct($data);
        if(empty($this->uploadDir))
            $this->uploadDir = 'uploads/img';
    }
    
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setUrl($url) { $this->url = $url; }
    
    public function getUrl() { return $this->url; }
    
    public function setAlt($alt) { $this->alt = $alt; }
    
    public function getAlt() { return $this->alt; }
    
    public function getFile() { return $this->file; }
    
    public function setFile(UploadedFile $file = null) {
        $this->file = $file;
        
        $this->tempFilename = $this->id === null ?: $this->id . '.'.$this->url;
        $this->alt = null;
        $this->url = null;
    }
    
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preUpload() {
        if(null === $this->file)
            return;
        
        if(preg_match('/<|>/', $this->file->getClientOriginalName()))
            $this->alt = 'Image';
        else
            $this->alt = $this->file->getClientOriginalName();
        $this->url = $this->file->getClientOriginalExtension();
    }
    
    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function upload() {
        if(null === $this->file)
            return;
        if(null !== $this->tempFilename && file_exists($this->tempFilename))
            unlink($this->tempFilename);
        
        $this->file->move($this->getUploadRootDir(), $this->id . '.' .$this->url);
    }
    
    /**
     * @ORM\PreRemove
     */
    public function preRemoveUpload() {
        $this->tempFilename = $this->getUploadRootDir() . '/' .$this->id . '.'. $this->url;
    }
    
    /**
     * @ORM\PostRemove
     */
    public function removeUpload() {
        if(file_exists($this->tempFilename))
            unlink($this->tempFilename);
    }
    
    public function getUploadRootDir() {
        return __DIR__. '/../../../../web/' . $this->uploadDir;
    }
    
    public function getUploadDir() {
        return $this->uploadDir;
    }
    
    public function setUploadDir($uploadDir) {
        $this->uploadDir = $uploadDir;
    }
    
    public function getSrc() {
        return $this->uploadDir . '/'.$this->id.'.'.$this->url;
    }
}
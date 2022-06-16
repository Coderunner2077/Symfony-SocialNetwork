<?php
// src/LEF/GroupBundle/Entity/Group.php

namespace LEF\GroupBundle\Entity;

use LEF\CoreBundle\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use FOS\UserBundle\Model\GroupInterface;
use LEF\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Table(name="lef_group")
 * @ORM\Entity(repositoryClass="LEF\GroupBundle\Repository\GroupRepository")
 * @UniqueEntity(fields="name")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Tree(type="nested")
 */
class Group extends Entity implements GroupInterface {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     * @Assert\NotBlank(message="group.notblank.name")
     * @Assert\Length(min=5, max=50, minMessage="group.length.name_min", 
     *     maxMessage="group.length.name_max"
     * )
     * @Assert\Regex(pattern="/[^a-z'\.\s-]/i", match=false, message="group.regex.name")
     * @Assert\Regex(pattern="/\s\s|\.\.|--|''/", match=false, message="group.regex.name_row")
     * @Assert\Regex(pattern="/^[A-Z]/", message="group.regex.name_ucfirst")
     */
    protected $name;
    
    /**
     * @ORM\OneToMany(targetEntity="LEF\GroupBundle\Entity\MemberPrivilege", mappedBy="group")
     */
    protected $privileges;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\GroupCategory", cascade={"persist"})
     * @ORM\JoinColumn(name="group_category_id", nullable=false)
     */
    protected $groupCategory;
    
    /**
     * @ORM\Column(name="enabled", type="boolean", options={"default": true})
     */
    protected $enabled = true;
    
    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;
 
    /**
     * @ORM\OneToOne(targetEntity="LEF\CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected $logo;  
    
    /**
     * @ORM\OneToOne(targetEntity="LEF\CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    protected $background;
    
    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;
    
    /**
     * @ORM\Column(type="boolean", options={"default": false})
     * @Assert\NotNull
     */
    protected $democratic = false;
    
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;
    
    
    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;
    
    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;
    
    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;
    
    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="Group", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;
    
    /**
     * @ORM\Column(name="vacancies", type="integer", nullable=true)
     */
    protected $vacancies;
    
    /**
     * @ORM\Column(name="nb_followers", type="integer", nullable=true)
     */
    protected $nbFollowers;
    
    /**
     * @ORM\Column(name="about", type="string", length=255)
     * @Assert\Length(min=35, max=255)
     */
    protected $about;
    
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
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setUpdatedAt(new \DateTime());

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
    
    public function addPrivilege(MemberPrivilege $privilege)
    {
        $this->privileges[] = $privilege;      

        return $this;
    }

    public function removePrivilege(MemberPrivilege $privilege)
    {
        $this->privileges->removeElement($privilege);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrivileges()
    {
        return $this->privileges;
    }
    
    public function hasPrivilege(MemberPrivilege $privilege) {
        return $this->$privileges->contains($privilege);
    }

    public function setRoles(array $roles) { }
    
    public function getRoles() { }
    
    public function addRole($role) { } 
    
    public function removeRole($role) { }
    
    public function hasRole($role) { }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Group
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        $this->setUpdatedAt(new \DateTime());

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Group
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    public function logoSrc() {
        return $this->logo ? $this->logo->getSrc() : 'img/avatar.png';
    }

    /**
     * Set logo
     *
     * @param \LEF\CoreBundle\Entity\Image $logo
     *
     * @return Group
     */
    public function setLogo(\LEF\CoreBundle\Entity\Image $logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return \LEF\CoreBundle\Entity\Image
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Group
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function initialize() {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * Set groupCategory.
     *
     * @param \LEF\GroupBundle\Entity\GroupCategory $groupCategory
     *
     * @return Group
     */
    public function setGroupCategory(\LEF\GroupBundle\Entity\GroupCategory $groupCategory)
    {
        $this->groupCategory = $groupCategory;

        return $this;
    }

    /**
     * Get groupCategory.
     *
     * @return \LEF\GroupBundle\Entity\GroupCategory
     */
    public function getGroupCategory()
    {
        return $this->groupCategory;
    }

 
    public function setBackground(\LEF\CoreBundle\Entity\Image $bg = null) {
        $this->background = $bg;
    }
    
    public function getBackground() {
        return $this->background;
    }
    
    /**
     * Set parent.
     *
     * @param \LEF\GroupBundle\Entity\Group|null $parent
     *
     * @return Group
     */
    public function setParent(\LEF\GroupBundle\Entity\Group $parent = null)
    {
        $this->parent = $parent;
        
        return $this;
    }
    
    /**
     * Get parent.
     *
     * @return \LEF\GroupBundle\Entity\Group|null
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    public function getAlias() {
        return '@' . strtolower($this->name);
    }

    /**
     * Set lft.
     *
     * @param int $lft
     *
     * @return Group
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft.
     *
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt.
     *
     * @param int $rgt
     *
     * @return Group
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt.
     *
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set lvl.
     *
     * @param int $lvl
     *
     * @return Group
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl.
     *
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set root.
     *
     * @param \LEF\GroupBundle\Entity\Group|null $root
     *
     * @return Group
     */
    public function setRoot(\LEF\GroupBundle\Entity\Group $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root.
     *
     * @return \LEF\GroupBundle\Entity\Group|null
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Add child.
     *
     * @param \LEF\GroupBundle\Entity\Group $child
     *
     * @return Group
     */
    public function addChild(\LEF\GroupBundle\Entity\Group $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child.
     *
     * @param \LEF\GroupBundle\Entity\Group $child
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeChild(\LEF\GroupBundle\Entity\Group $child)
    {
        return $this->children->removeElement($child);
    }

    /**
     * Get children.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }   

    /**
     * Set vacancies.
     *
     * @param int|null $vacancies
     *
     * @return Group
     */
    public function setVacancies($vacancies = null)
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
    
    /**
     * Set nbFollowers.
     *
     * @param int|null $nbFollowers
     *
     * @return User
     */
    public function setNbFollowers($nbFollowers = null)
    {
        $this->nbFollowers = $nbFollowers;
        
        return $this;
    }
    
    /**
     * Get nbFollowers.
     *
     * @return int|null
     */
    public function getNbFollowers()
    {
        return (int) $this->nbFollowers;
    }
    
    public function incrementFollowers() {
        $this->nbFollowers++;
    }
    
    public function decrementFollowers() {
        $this->nbFollowers--;
    }    
    
    public function addVacancies(array $masks) {
        $this->vacancies = 0;
        foreach($masks as $mask)
            $this->vacancies |= $mask;
    }
    
    public function setAbout($about) {
        $this->about = $about;
        return $this;
    }
    
    public function getAbout() { return $this->about; }
    
    public function setDemocratic($bool) {
        $this->democratic = $bool;
        return $this;
    }
    
    public function getDemocratic() { return $this->democratic; }
    
    public function isDemocratic() { return $this->democratic; }
}

<?php
namespace LEF\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity; 
use LEF\CoreBundle\Entity\LikesTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/** 
 * @ORM\Table(name="group_post")
 * @ORM\Entity(repositoryClass="LEF\GroupBundle\Repository\GroupPostRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Tree(type="nested")
 */
class GroupPost extends Entity {  
    
   use LikesTrait;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
	protected $author;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\Group", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 * @Assert\Valid()
	 * @Assert\NotBlank()
	 */
	protected $group;
	
	/**
	 * @ORM\Column(name="content", type="string", length=255)
	 * @Assert\Length(max=255, min=4)
	 * @Assert\NotBlank()
	 */
	protected $content;
	
	/**
	 * @ORM\Column(name="published_at", type="datetime")
	 */
	protected $publishedAt;
	
	/**
	 * @ORM\Column(name="likes", type="integer", options={"unsigned":true, "default":0}, nullable=true)
	 */
	protected $likes;
	
	/**
	 * @ORM\Column(name="dislikes", type="integer", options={"unsigned":true, "default":0}, nullable=true)
	 */
	protected $dislikes;
	
    /**
     * @ORM\Column(name="public_post", type="boolean")
     * @Assert\NotNull
     */
	protected $publicPost = true;
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="nb_comments", type="integer", nullable=true)
	 */
	protected $nbComments;
	
	/**
	 * @ORM\OneToOne(targetEntity="LEF\CoreBundle\Entity\Image", cascade={"persist", "remove"})
	 * @Assert\Valid
	 */
	protected $image;
	
    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
	private $lvl;
	
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
	 * @Gedmo\TreeRoot
	 * @ORM\ManyToOne(targetEntity="GroupPost")
	 * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $root;
	
	/**
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="GroupPost", inversedBy="children", cascade={"persist"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $parent;
	
	/**
	 * @ORM\OneToMany(targetEntity="GroupPost", mappedBy="parent")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	private $children;
	
	/**
	 * @ORM\Column(name="repost", type="boolean")
	 */
	protected $repost = false;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $editor;
	
	/**
	 * @ORM\Column(name="banned", type="boolean")
	 */
	protected $banned = false;
	
	protected $edited;
	
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
     * Set content
     *
     * @param string $content
     *
     * @return GroupPost
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     *
     * @return GroupPost
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return GroupPost
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }
    
    /**
     * Get edited
     *
     * @return \DateTime
     */
    public function isEdited()
    {
        if(!empty($this->edited))
            return $this->edited;
        return !empty($this->editor);
    }

    /**
     * Set user
     *
     * @param \LEF\UserBundle\Entity\User $user
     *
     * @return GroupPost
     */
    public function setAuthor(\LEF\UserBundle\Entity\User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set publicPost
     *
     * @param boolean $publicPost
     *
     * @return GroupPost
     */
    public function setPublic($publicPost)
    {
        $this->publicPost = $publicPost;

        return $this;
    }

    /**
     * Get publicPost
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->publicPost;
    }

    /**
     * Set publicPost
     *
     * @param boolean $publicPost
     *
     * @return GroupPost
     */
    public function setPublicPost($publicPost)
    {
        $this->publicPost = $publicPost;

        return $this;
    }

    /**
     * Get publicPost
     *
     * @return boolean
     */
    public function getPublicPost()
    {
        return $this->publicPost;
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

    /**
     * Set nbComments.
     *
     * @param int $nbComments
     *
     * @return GroupPost
     */
    public function setNbComments($nbComments)
    {
        $this->nbComments = $nbComments;

        return $this;
    }

    /**
     * Get nbComments.
     *
     * @return int
     */
    public function getNbComments()
    {
        return $this->nbComments;
    }

    /**
     * Set parent.
     *
     * @param \LEF\GroupBundle\Entity\GroupPost|null $parent
     *
     * @return GroupPost
     */
    public function setParent(\LEF\GroupBundle\Entity\GroupPost $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent.
     *
     * @return \LEF\GroupBundle\Entity\GroupPost|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child.
     *
     * @param \LEF\GroupBundle\Entity\GroupPost $child
     *
     * @return GroupPost
     */
    public function addChild(\LEF\GroupBundle\Entity\GroupPost $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child.
     *
     * @param \LEF\GroupBundle\Entity\GroupPost $child
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeChild(\LEF\GroupBundle\Entity\GroupPost $child)
    {
        $child->setParent(null);
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
    
    public function incrementComments() {
        $this->nbComments++;
    }
    
    public function decrementComments() {
        $this->nbComments--;
    }

    /**
     * Set lvl.
     *
     * @param int|null $lvl
     *
     * @return GroupPost
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl.
     *
     * @return int|null
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set lft.
     *
     * @param int $lft
     *
     * @return GroupPost
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
     * @return GroupPost
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
     * Set root.
     *
     * @param \LEF\GroupBundle\Entity\GroupPost|null $root
     *
     * @return GroupPost
     */
    public function setRoot(\LEF\GroupBundle\Entity\GroupPost $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root.
     *
     * @return \LEF\GroupBundle\Entity\GroupPost|null
     */
    public function getRoot()
    {
        return $this->root;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $this->publishedAt = new \DateTime();
        $parent = $this->parent;
        while($parent instanceof GroupPost) {
            $parent->incrementComments();
            if($parent->isBanned())
                break;
            $parent = $parent->getParent();
        }
    }
    
    /**
     * @ORM\PreRemove
     */
    public function preRemove() {
        if($this->isBanned())
            return;
        $parent = $this->parent;
        while($parent instanceof GroupPost) {
            $parent->decrementComments();
            if($parent->isBanned())
                break;
            $parent = $parent->getParent();
        }
    }
    
    /**
     * Set repost.
     *
     * @param bool $repost
     *
     * @return GroupPost
     */
    public function setRepost($repost)
    {
        $this->repost = $repost;

        return $this;
    }

    /**
     * Get repost.
     *
     * @return bool
     */
    public function getRepost()
    {
        return $this->repost;
    }
    
    /**
     * Get repost.
     *
     * @return bool
     */
    public function isRepost() {
        return $this->repost;
    }

    /**
     * Set editor.
     *
     * @param \LEF\UserBundle\Entity\User|null $editor
     *
     * @return GroupPost
     */
    public function setEditor(\LEF\UserBundle\Entity\User $editor = null)
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * Get editor.
     *
     * @return \LEF\UserBundle\Entity\User|null
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * Set banned.
     *
     * @param bool $banned
     *
     * @return GroupPost
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    /**
     * Get banned.
     *
     * @return bool
     */
    public function getBanned()
    {
        return $this->banned;
    }
    
    public function isBanned() {
        return $this->banned;
    }
    
    public function setImage(\LEF\CoreBundle\Entity\Image $image = null) {
        $this->image = $image;
        return $this;
    }
    
    public function getImage() { return $this->image; }
}

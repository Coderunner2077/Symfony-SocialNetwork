<?php
namespace LEF\PostBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity; 
use LEF\CoreBundle\Entity\LikesTrait; 
use Gedmo\Mapping\Annotation as Gedmo;

/** 
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="LEF\PostBundle\Repository\PostRepository")
 * @Gedmo\Tree(type="nested")
 * @ORM\HasLifecycleCallbacks
 */
class Post extends Entity {   
    
    use LikesTrait;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
	protected $author;
	
	/**
	 * @ORM\Column(name="content", type="string", length=255)
	 * @Assert\Length(min=4, max=255)
	 * @Assert\NotBlank()
	 */
	protected $content;
	
	/**
	 * @ORM\Column(name="published_at", type="datetime", nullable=false)
	 */
	protected $publishedAt;
	
	/**
	 * @ORM\Column(type="integer", nullable=true, options={"unsigned": true, "default": 0})
	 */
	protected $likes;
	
	/**
	 * @ORM\Column(type="integer", nullable=true, options={"unsigned": true, "default": 0})
	 */
	protected $dislikes;
	
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
	 * @ORM\ManyToOne(targetEntity="Post")
	 * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $root;
	
	/**
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="Post", inversedBy="children", cascade={"persist"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $parent;
	
	/**
	 * @ORM\OneToMany(targetEntity="Post", mappedBy="parent")
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
	 * @return Post
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
	 * @return Post
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
	 * @return Post
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
	 * @return Post
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
	 * @return Post
	 */
	public function setPublic($publicPost)
	{
	    $this->publicPost = $publicPost;
	    
	    return $this;
	}
	
	/**
	 * Set nbComments.
	 *
	 * @param int $nbComments
	 *
	 * @return Post
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
	 * @param \LEF\PostBundle\Entity\Post|null $parent
	 *
	 * @return Post
	 */
	public function setParent(\LEF\PostBundle\Entity\Post $parent = null)
	{
	    $this->parent = $parent;
	    return $this;
	}
	
	/**
	 * Get parent.
	 *
	 * @return \LEF\PostBundle\Entity\Post|null
	 */
	public function getParent()
	{
	    return $this->parent;
	}
	
	/**
	 * Add child.
	 *
	 * @param \LEF\PostBundle\Entity\Post $child
	 *
	 * @return Post
	 */
	public function addChild(\LEF\PostBundle\Entity\Post $child)
	{
	    $this->children[] = $child;
	    $child->setParent($this);
	    
	    return $this;
	}
	
	/**
	 * Remove child.
	 *
	 * @param \LEF\PostBundle\Entity\Post $child
	 *
	 * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
	 */
	public function removeChild(\LEF\PostBundle\Entity\Post $child)
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
	 * @return Post
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
	 * @return Post
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
	 * @return Post
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
	 * @param \LEF\PostBundle\Entity\Post|null $root
	 *
	 * @return Post
	 */
	public function setRoot(\LEF\PostBundle\Entity\Post $root = null)
	{
	    $this->root = $root;
	    
	    return $this;
	}
	
	/**
	 * Get root.
	 *
	 * @return \LEF\PostBundle\Entity\Post|null
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
	    while($parent instanceof Post) {
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
	    $parent = $this->getParent();
	    while($parent instanceof Post) {
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
	 * @return Post
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
	 * @return Post
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
	 * @return Post
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

<?php
namespace LEF\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity;
use LEF\CoreBundle\Entity\LikesTrait;

/** 
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="LEF\ArticleBundle\Repository\ArticleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Article extends Entity {   
    
    use LikesTrait;
    
    /**
     * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author", nullable=false)
     */
	protected $author;
	
	/**
	 * @ORM\Column(name="title", type="string", length=150)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=5, max=150)
	 */
	protected $title;
	
	/**
	 * @ORM\Column(name="content", type="text")
	 * @Assert\NotBlank()
	 * @Assert\Length(min=300)
	 */
	protected $content;
	
	/**
	 * @ORM\Column(name="published_at", type="datetime", nullable=true)
	 */
	protected $publishedAt;
	
	/**
	 * @ORM\Column(name="updated_at", type="datetime", nullable=true)
	 */	
	protected $updatedAt;
	
	/**
	 * @ORM\OneToOne(targetEntity="LEF\CoreBundle\Entity\Image", cascade={"persist", "remove"})
	 * @Assert\Valid
	 */
	protected $image;
	
	/**
	 * @ORM\Column(name="allow_comments", type="boolean")
	 */
	protected $allowComments = false;
	
	/**
     * @ORM\Column(name="intro", type="string", length=255)
     * @Assert\NotBlank()
	 * @Assert\Length(min=30, max=255)
     * 
	 */
	protected $intro;
    
	/**
	 * @ORM\OneToMany(targetEntity="LEF\ArticleBundle\Entity\Comment", mappedBy="article")
	 */
	protected $comments;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Category", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $category;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\GroupBundle\Entity\Group", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $group;
	
	/**
	 * @ORM\Column(name="likes", type="integer", options={"unsigned":true, "default":0}, nullable=true)
	 */
	protected $likes;
	
	/**
	 * @ORM\Column(name="dislikes", type="integer", options={"unsigned":true, "default":0}, nullable=true)
	 */
	protected $dislikes;
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;		
	
	/**
	 * @ORM\Column(name="published", type="boolean", options={"default": false})
	 */
	protected $published = false;
	
	/**
	 * @ORM\Column(name="nb_comments", type="integer", nullable=true)
	 */
	protected $nbComments;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $editor;
	
	/**
	 * @ORM\Column(type="smallint", options={"default": 100})
	 * @Assert\Range(min=0, max=100)
	 */
	protected $display = 100;
	
	public function isEdited()
	{
	    return !empty($this->editor);
	}
    /**
     * Set title
     *
     * @param string $title
     *
     * @return Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Article
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
     * @return Article
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Article
     */
    public function setUpdatedAt($updatedAt)
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

    /**
     * Set allowComments
     *
     * @param boolean $allowComments
     *
     * @return Article
     */
    public function setAllowComments($allowComments)
    {
        $this->allowComments = $allowComments;

        return $this;
    }

    /**
     * Get allowComments
     *
     * @return boolean
     */
    public function getAllowComments()
    {
        return $this->allowComments;
    }

    /**
     * Set intro
     *
     * @param string $intro
     *
     * @return Article
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * Get intro
     *
     * @return string
     */
    public function getIntro()
    {
        return $this->intro;
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

    /**
     * Set author
     *
     * @param \LEF\UserBundle\Entity\User $author
     *
     * @return Article
     */
    public function setAuthor(\LEF\UserBundle\Entity\User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->author;
    }
    
    /**
     * Add comment
     *
     * @param \LEF\ArticleBundle\Entity\Comment $comment
     *
     * @return Article
     */
    public function addComment(\LEF\ArticleBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;
        $comment->setArticle($this);

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \LEF\ArticleBundle\Entity\Comment $comment
     */
    public function removeComment(\LEF\ArticleBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
        $comment->setArticle(null);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set image
     *
     * @param \LEF\CoreBundle\Entity\Image $image
     *
     * @return Article
     */
    public function setImage(\LEF\CoreBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \LEF\CoreBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set category
     *
     * @param \LEF\ArticleBundle\Entity\Category $category
     *
     * @return Article
     */
    public function setCategory(\LEF\ArticleBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \LEF\ArticleBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set group
     *
     * @param \LEF\GroupBundle\Entity\Group $group
     *
     * @return Article
     */
    public function setGroup(\LEF\GroupBundle\Entity\Group $group)
    {
        $this->group = $group;

        return $this;
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
     * Set published
     *
     * @param boolean $published
     *
     * @return Article
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        if($this->published === true)
            $this->publishedAt = new \DateTime();
    }
    
    public function isPublished() { return $this->published; }
    
    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        if($this->published === true) {
            if($this->publishedAt === null)
                $this->publishedAt = new \DateTime();
            else 
                $this->updatedAt = new \DateTime();
        }
    }

    /**
     * Set nbComments.
     *
     * @param int $nbComments
     *
     * @return Article
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
     * Get author.
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }
    
    public function incrementComments() {
        $this->nbComments++;
    }
    
    public function decrementComments() {
        $this->nbComments--;
    }

    /**
     * Set editor.
     *
     * @param \LEF\UserBundle\Entity\User|null $editor
     *
     * @return Article
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
    
    public function setDisplay($display) { $this->display = $display; return $this; }
    
    public function getDisplay() { return $this->display; }
    
    public function isViewable() { return $this->display == 100; } 
}

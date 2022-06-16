<?php
namespace LEF\ArticleBundle\Entity;

use LEF\CoreBundle\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; 
use LEF\CoreBundle\Entity\LikesTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="LEF\ArticleBundle\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Tree(type="nested")
 */
class Comment extends Entity { 
    
    use LikesTrait;
    
    /**
     * @ORM\Column(name="content", type="string", length=255)
	 * @Assert\NotBlank()
     * @Assert\Length(max=255, min=2)
     */
	protected $content;
	
	/**
	 * @ORM\Column(name="published_at", type="datetime", nullable=false)
	 */
	protected $publishedAt;
		
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
	 */
	protected $author;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\ArticleBundle\Entity\Article", inversedBy="comments")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $article;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $likes;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $dislikes;
	
	
	/**
	 * @ORM\Column(name="nb_comments", type="integer", nullable=true)
	 */
	protected $nbComments;
	
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
	 * @ORM\ManyToOne(targetEntity="Comment")
	 * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $root;
	
	/**
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children", cascade={"persist"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $parent;
	
	/**
	 * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	private $children;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $editor;
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
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
     * @return Comment
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
     * @return Comment
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
     * Set user
     *
     * @param \LEF\UserBundle\Entity\User $user
     *
     * @return Comment
     */
    public function setUser(\LEF\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set article
     *
     * @param \LEF\ArticleBundle\Entity\Article $article
     *
     * @return Comment
     */
    public function setArticle(\LEF\ArticleBundle\Entity\Article $article = null)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \LEF\ArticleBundle\Entity\Article
     */
    public function getArticle()
    {
        return $this->article;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $this->article->incrementComments();
        $this->publishedAt = new \DateTime();
        $parent = $this->parent;
        while($parent instanceof Comment) {
            $parent->incrementComments();
            $parent = $parent->getParent();
        }
    }

    /**
     * Set parent.
     *
     * @param \LEF\ArticleBundle\Entity\Comment|null $parent
     *
     * @return Comment
     */
    public function setParent(\LEF\ArticleBundle\Entity\Comment $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \LEF\ArticleBundle\Entity\Comment|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child.
     *
     * @param \LEF\ArticleBundle\Entity\Comment $child
     *
     * @return Comment
     */
    public function addChild(\LEF\ArticleBundle\Entity\Comment $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child.
     *
     * @param \LEF\ArticleBundle\Entity\Comment $child
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeChild(\LEF\ArticleBundle\Entity\Comment $child)
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
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return Comment
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
    public function getEdited()
    {
        return $this->edited;
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
    
    public function incrementComments() {
        $this->nbComments++;
    }
    
    public function decrementComments() {
        $this->nbComments--;
    }

    /**
     * Set nbComments.
     *
     * @param int|null $nbComments
     *
     * @return Comment
     */
    public function setNbComments($nbComments = null)
    {
        $this->nbComments = $nbComments;

        return $this;
    }

    /**
     * Get nbComments.
     *
     * @return int|null
     */
    public function getNbComments()
    {
        return $this->nbComments;
    }

    /**
     * Set lvl.
     *
     * @param int $lvl
     *
     * @return Comment
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
     * Set lft.
     *
     * @param int $lft
     *
     * @return Comment
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
     * @return Comment
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
     * Set author.
     *
     * @param \LEF\UserBundle\Entity\User|null $author
     *
     * @return Comment
     */
    public function setAuthor(\LEF\UserBundle\Entity\User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return \LEF\UserBundle\Entity\User|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set root.
     *
     * @param \LEF\ArticleBundle\Entity\Comment|null $root
     *
     * @return Comment
     */
    public function setRoot(\LEF\ArticleBundle\Entity\Comment $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root.
     *
     * @return \LEF\ArticleBundle\Entity\Comment|null
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set editor.
     *
     * @param \LEF\UserBundle\Entity\User|null $editor
     *
     * @return Comment
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
}

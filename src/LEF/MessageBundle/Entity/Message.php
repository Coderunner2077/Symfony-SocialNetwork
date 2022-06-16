<?php
namespace LEF\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LEF\CoreBundle\Entity\Entity; 

/** 
 * @ORM\Table(name="lef_message")
 * @ORM\Entity(repositoryClass="LEF\MessageBundle\Repository\MessageRepository")
 */
class Message extends Entity {   	
	/**
	 * @ORM\Column(name="content", type="string", length=255)
	 * @Assert\Length(max=255, maxMessage="length.max.message.content")
	 */
	protected $content;
	
	/**
	 * @ORM\Column(name="published_at", type="datetime", nullable=false)
	 */
	protected $publishedAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
	 */
	protected $sender;
	
	/**
	 * @ORM\ManyToOne(targetEntity="LEF\UserBundle\Entity\User")
	 */
	protected $receiver;
	
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
     * Set content
     *
     * @param string $content
     *
     * @return Message
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
     * @return Message
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
     * Set sender
     *
     * @param \LEF\UserBundle\Entity\User $sender
     *
     * @return Message
     */
    public function setSender(\LEF\UserBundle\Entity\User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set receiver
     *
     * @param \LEF\UserBundle\Entity\User $receiver
     *
     * @return Message
     */
    public function setReceiver(\LEF\UserBundle\Entity\User $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \LEF\UserBundle\Entity\User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }
}

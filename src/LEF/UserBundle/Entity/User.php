<?php
// src/LEF/UserBundle/Entity/User.php

namespace LEF\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="LEF\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="fullname", type="string", length=50, nullable=false)
     * @Assert\NotBlank(message="user.notblank.fullname", groups={"Registration", "Profile"})
     * @Assert\Length(min=5, max=50, minMessage="user.length.fullname_min", 
     *     maxMessage="user.length.fullname_max", groups={"Registration", "Profile"}
     * )
     * @Assert\Regex(pattern="/[^a-záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ'\.\s-]/i", match=false, message="user.regex.fullname", groups={"Registration", "Profile"})
     * @Assert\Regex(pattern="/\s\s|\.{2}|-{2}/", match=false, message="user.regex.fullname_row", groups={"Registration", "Profile"})
     * @Assert\Regex(pattern="/[\s\b][a-zA-Z][^a-zA-Z']|[\s\b][a-zA-Z]$/", match=false, message="user.regex.fullname_letter", groups={"Registration", "Profile"})
     * @Assert\Regex(pattern="/[\s\b\.-][a-z\.-]|^[a-z'\s\.-]/", match=false, message="user.regex.fullname_ucfirst", groups={"Registration", "Profile"})
     * @Assert\Regex(pattern="/\s/",message="user.regex.fullname_nospace", groups={"Registration", "Profile"})
     */
    protected $fullname;
    
    /**
     * @ORM\ManyToMany(targetEntity="LEF\UserBundle\Entity\User", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="user_followed", 
     *                joinColumns={@ORM\JoinColumn(name="follower_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="followed_id", referencedColumnName="id")}
     * )       
     */
    protected $followed;
    
    /**
     * @ORM\ManyToMany(targetEntity="LEF\UserBundle\Entity\User", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="user_blocked",
     *                joinColumns={@ORM\JoinColumn(name="blocker_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="blocked_id", referencedColumnName="id")}
     * )
     */
    protected $blocked;
    
    /**
     * @ORM\ManyToMany(targetEntity="LEF\GroupBundle\Entity\Group", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="lef_group_followed",
     *                joinColumns={@ORM\JoinColumn(name="follower_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $followedGroups;
    
    /**
     * @ORM\OneToOne(targetEntity="LEF\CoreBundle\Entity\Image", cascade={"persist"})
     * @Assert\Valid
     */
    protected $avatar;
    
    /**
     * @ORM\Column(name="nb_followers", type="integer", nullable=true)
     */
    protected $nbFollowers;
    
    
    /**
     * Set fullname
     *
     * @param string $fullname
     *
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname
     *
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }
    
    /**
     * Set avatar
     *
     * @param \LEF\CoreBundle\Entity\Image $avatar
     *
     * @return User
     */
    public function setAvatar(\LEF\CoreBundle\Entity\Image $avatar = null)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return \LEF\CoreBundle\Entity\Image
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    public function avatarSrc() {
        return $this->avatar ? $this->avatar->getSrc() : 'img/avatar.png';
    }

    /**
     * Add followed
     *
     * @param \LEF\UserBundle\Entity\User $followed
     *
     * @return User
     */
    public function addFollowed(\LEF\UserBundle\Entity\User $followed)
    {
        $this->followed[] = $followed;
        $followed->incrementFollowers();
        
        return $this;
    }

    /**
     * Remove followed
     *
     * @param \LEF\UserBundle\Entity\User $followed
     */
    public function removeFollowed(\LEF\UserBundle\Entity\User $followed)
    {
        $followed->decrementFollowers();
        return $this->followed->removeElement($followed);
    }

    /**
     * Get followed
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFollowed()
    {
        return $this->followed;
    }
    
    
    /**
     * Add blocked
     *
     * @param \LEF\UserBundle\Entity\User $blocked
     *
     * @return User
     */
    public function addBlocked(\LEF\UserBundle\Entity\User $blocked)
    {
        $this->blocked[] = $blocked;
        return $this;
    }
    
    /**
     * Remove blocked
     *
     * @param \LEF\UserBundle\Entity\User $blocked
     */
    public function removeBlocked(\LEF\UserBundle\Entity\User $blocked)
    {
        return $this->blocked->removeElement($blocked);
    }
    
    /**
     * Get blocked
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlocked()
    {
        return $this->blocked;
    }
    
    public function getAlias() {
        return '@' . ($this->username);
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
    
    /**
     * Add followedGroup.
     *
     * @param \LEF\GroupBundle\Entity\Group $group
     *
     * @return Group
     */
    public function addFollowedGroup(\LEF\GroupBundle\Entity\Group $group)
    {
        $this->followedGroups[] = $group;
        $group->incrementFollowers();
        
        return $this;
    }
    
    /**
     * Remove followedGroup.
     *
     * @param \LEF\GroupBundle\Entity\Group $group
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFollowedGroup(\LEF\GroupBundle\Entity\Group $group)
    {
        $group->decrementFollowers();
        return $this->followedGroups->removeElement($group);
    }
    
    /**
     * Get followedGroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFollowedGroups()
    {
        return $this->followedGroups;
    }
    
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }    
}

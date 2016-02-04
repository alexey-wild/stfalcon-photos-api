<?php

namespace ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Photo
 *
 * @ORM\Table(name="photo")
 * @ORM\Entity
 */
class Photo
{
    const FILE_PHOTO_PATH = 'uploads/photos';

    /**
     * @var int
     *
     * @ORM\Column(name="photo_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"list", "one", "add"})
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Groups({"list", "one", "add"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="original_name", type="string", length=255, nullable=true)
     *
     * @Groups({"list", "one", "add"})
     */
    private $originalName;

    /**
     * @var \ApiBundle\Entity\Tag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ApiBundle\Entity\Tag", inversedBy="photos", cascade={"remove", "persist"})
     * @ORM\JoinTable(name="photo_tag",
     *     joinColumns={@ORM\JoinColumn(name="photo_id", referencedColumnName="photo_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="tag_id")}
     * )
     *
     * @Groups({"list", "one"})
     */
    private $tags;

    // ========== MAGIC METHODS ==========

    /**
     * Constructor
     */
    public function __construct() {
        $this->tags = new ArrayCollection();
    }

    // ========== CUSTOM METHODS ==========

    /**
     * Get web url
     *
     * @return string
     */
    public function getWebUrl()
    {
        return self::FILE_PHOTO_PATH . '/' . $this->getName();
    }

    // ========== GETTER & SETTER ==========

    /**
     * Get id
     *
     * @return int
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
     * @return static
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

    /**
     * Set originalName
     *
     * @param string $originalName
     *
     * @return static
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Add tag
     *
     * @param \ApiBundle\Entity\Tag $tag
     *
     * @return static
     */
    public function addTag(\ApiBundle\Entity\Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            $tag->addPhoto($this);

            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \ApiBundle\Entity\Tag $tag
     */
    public function removeTag(\ApiBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection|\ApiBundle\Entity\Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }
}

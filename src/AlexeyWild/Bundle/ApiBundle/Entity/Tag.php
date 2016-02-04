<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity
 */
class Tag
{
    /**
     * @var int
     *
     * @ORM\Column(name="tag_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"list", "one"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @Groups({"list", "one"})
     */
    private $name;

    /**
     * @var \ApiBundle\Entity\Photo[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ApiBundle\Entity\Photo", mappedBy="tags")
     *
     * @Groups({"all"})
     */
    private $photos;

    // ========== MAGIC METHODS ==========

    /**
     * Constructor
     */
    public function __construct() {
        $this->photos = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getName();
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
     * @return Tag
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
     * Add photo
     *
     * @param \ApiBundle\Entity\Photo $photo
     *
     * @return Tag
     */
    public function addPhoto(\ApiBundle\Entity\Photo $photo)
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
        }

        return $this;
    }

    /**
     * Remove photo
     *
     * @param \ApiBundle\Entity\Photo $photo
     */
    public function removePhoto(\ApiBundle\Entity\Photo $photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection|\ApiBundle\Entity\Photo[]
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}

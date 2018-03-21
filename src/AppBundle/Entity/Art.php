<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Art
 *
 * @ORM\Table(name="art")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtRepository")
 */
class Art
{
    function __construct()
    {
        $this->artforms = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var  ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArtForm", mappedBy="art")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $artforms;

    /**
     * @var  ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArtWork", mappedBy="art")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $artworks;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createAt", type="datetime")
     */
    private $createAt;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateAt", type="datetime",nullable=true)
     */
    private $updateAt;

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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Art
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @return mixed
     */
    public function getArtForms(){
        return $this->artforms;
    }

    public function addArtForm(ArtForm $artforms) {
        $this->artforms[] = $artforms;
        return $this;
    }

    public function removeArtForm(ArtForm $artform) {
        $this->artforms->removeElement($artform);
    }

    /**
     * @return \DateTime
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * @param \DateTime $createAt
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * @param \DateTime $updateAt
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;
    }
}

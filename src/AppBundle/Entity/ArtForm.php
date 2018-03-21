<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArtForm
 *
 * @ORM\Table(name="art_form")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtFormRepository")
 */
class ArtForm
{
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
     * @var string
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Art",inversedBy="artforms")
     * @ORM\JoinColumn(nullable=true)
     */
    private $art;

    /**
     * @var  ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArtWork", mappedBy="artform")
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
     * @return ArtForm
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
    public function getLibelle(){
        return $this->libelle;
    }

    /**
     * @return mixed
     */
    public function getArt(){
        return $this->art;
    }

    /**
     * @param mixed $art
     */
    public function setArt($art){
        $this->art = $art;
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

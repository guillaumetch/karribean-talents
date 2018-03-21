<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Base;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{

    public function __construct()
    {
        parent::__construct();
        $this->artworks = new ArrayCollection();
    }
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="imageName", type="string", length=255,nullable=true)
     * @Assert\File(mimeTypes= {"image/*"},groups = {"create"})
     */
    protected $imageName;

    /**
     * @var string
     * @ORM\Column(name="nom", type="string", length=255,nullable=true)

     */
    protected $nom;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255,nullable=true)

     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateAt", type="datetime",nullable=true)
     */
    protected $updateAt;

    /**
     * @var  ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Artwork", mappedBy="user")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $artworks;

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
     * Set imageName
     *
     * @param string $imageName
     *
     * @return User
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     *
     * @return User
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = new \DateTime();

        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime
     */
    public function getUpdateAt(){
        return $this->updateAt;
    }


    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->setUsername(($email));
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription(){
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getArtWorks(){
        return $this->artworks;
    }

    public function addArtWork(Artwork $artwork) {
        $this->artworks[] = $artwork;
        return $this;
    }

    public function removeArtWork(Artwork $artwork) {
        $this->artworks->removeElement($artwork);
    }
}


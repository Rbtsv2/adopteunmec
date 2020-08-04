<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParsingIdRepository")
 */
class ParsingId
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $urlid;

    /**
     * @ORM\Column(type="string")
     */
    private $city;

    /**
     * @ORM\Column(type="string")
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string")
     */
    private $age;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isvisited = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Informations", mappedBy="relation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="informations_id", referencedColumnName="id", nullable=true)
     */
    private $informations = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Images", mappedBy="relation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="images_id", referencedColumnName="id", nullable=true)
     */
    private $images = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $isInfo;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lng;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lat;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sexe;

    public function __construct()
    {
        $this->informations = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlid(): ?string
    {
        return $this->urlid;
    }

    public function setUrlid(string $urlid): self
    {
        $this->urlid = $urlid;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(string $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getIsvisited(): ?bool
    {
        return $this->isvisited;
    }

    public function setIsvisited(bool $isvisited): self
    {
        $this->isvisited = $isvisited;

        return $this;
    }

    /**
     * @return Collection|Informations[]
     */
    public function getInformations(): Collection
    {
        return $this->informations;
    }

    public function addInformation(Informations $information): self
    {
        if (!$this->informations->contains($information)) {
            $this->informations[] = $information;
            $information->setRelation($this);
        }

        return $this;
    }

    public function removeInformation(Informations $information): self
    {
        if ($this->informations->contains($information)) {
            $this->informations->removeElement($information);
            // set the owning side to null (unless already changed)
            if ($information->getRelation() === $this) {
                $information->setRelation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Images[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setRelation($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getRelation() === $this) {
                $image->setRelation(null);
            }
        }

        return $this;
    }

   
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsInfo(): ?int
    {
        return $this->isInfo;
    }

    public function setIsInfo(?int $isInfo): self
    {
        $this->isInfo = $isInfo;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(?string $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getSexe(): ?bool
    {
        return $this->sexe;
    }

    public function setSexe(?bool $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }



  

}

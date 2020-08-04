<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImagesRepository")
 */
class Images
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ParsingId", inversedBy="images")
     * @ORM\JoinColumn(name="relation_id", referencedColumnName="id")
     */
    private $relation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }


    public function getRelation(): ?ParsingId
    {
        return $this->relation;
    }

    public function setRelation(?ParsingId $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

  
}

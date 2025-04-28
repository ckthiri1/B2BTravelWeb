<?php
// src/Entity/Voyage.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Voyage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "VID", type: "integer")]
    private ?int $VID = null;

    #[ORM\Column(name:'depart' ,type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le lieu de départ est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le lieu de départ ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $depart = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le lieu d'arrivée est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le lieu d'arrivée ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Expression(
        "this.getDepart() !== this.getDestination()",
        message: "Le lieu de départ et d'arrivée ne peuvent pas être identiques."
    )]
    private ? string $Destination = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description du voyage est obligatoire.")]
    #[Assert\Length(
        max: 2000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ? string $Description = null;

    #[ORM\OneToMany(mappedBy: "voyage", targetEntity: Vol::class)] 
    private Collection $vols;

    public function __construct()
    {
        $this->vols = new ArrayCollection();
        $this->depart = null; // or some default value
        $this->Destination = null; 
        $this->Description = null; 
    }

    public function getVID()
    {
        return $this->VID;
    }

    public function setVID($value)
    {
        $this->VID = $value;
    }

    public function getDepart()
    {
        return $this->depart;
    }

    public function setDepart($value)
    {
        $this->depart = $value;
    }

    public function getDestination()
    {
        return $this->Destination;
    }

    public function setDestination($value)
    {
        $this->Destination = $value;
    }

    public function getDescription()
    {
        return $this->Description;
    }

    public function setDescription($value)
    {
        $this->Description = $value;
    }

    public function getVols(): Collection
    {
        return $this->vols;
    }

    public function addVol(Vol $vol): self
    {
        if (!$this->vols->contains($vol)) {
            $this->vols[] = $vol;
            $vol->setIdVoyage($this);
        }

        return $this;
    }

    public function removeVol(Vol $vol): self
    {
        if ($this->vols->removeElement($vol)) {
            // set the owning side to null (unless already changed)
            if ($vol->getIdVoyage() === $this) {
                $vol->setIdVoyage(null);
            }
        }

        return $this;
    }
}
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Vol;

#[ORM\Entity]
class Voyage
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $VID;

    #[ORM\Column(type: "string", length: 255)]
    private string $depart;

    #[ORM\Column(type: "string", length: 255)]
    private string $Destination;

    #[ORM\Column(type: "text")]
    private string $Description;

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

    #[ORM\OneToMany(mappedBy: "idVoyage", targetEntity: Vol::class)]
    private Collection $vols;

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

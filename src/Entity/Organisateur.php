<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Evennement;

#[ORM\Entity]
class Organisateur
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $IDOr;

    #[ORM\Column(type: "string", length: 255)]
    private string $NomOr;

    #[ORM\Column(type: "string", length: 255)]
    private string $Contact;

    public function getIDOr()
    {
        return $this->IDOr;
    }

    public function setIDOr($value)
    {
        $this->IDOr = $value;
    }

    public function getNomOr()
    {
        return $this->NomOr;
    }

    public function setNomOr($value)
    {
        $this->NomOr = $value;
    }

    public function getContact()
    {
        return $this->Contact;
    }

    public function setContact($value)
    {
        $this->Contact = $value;
    }

    #[ORM\OneToMany(mappedBy: "IDOr", targetEntity: Evennement::class)]
    private Collection $evennements;

        public function getEvennements(): Collection
        {
            return $this->evennements;
        }
    
        public function addEvennement(Evennement $evennement): self
        {
            if (!$this->evennements->contains($evennement)) {
                $this->evennements[] = $evennement;
                $evennement->setIDOr($this);
            }
    
            return $this;
        }
    
        public function removeEvennement(Evennement $evennement): self
        {
            if ($this->evennements->removeElement($evennement)) {
                // set the owning side to null (unless already changed)
                if ($evennement->getIDOr() === $this) {
                    $evennement->setIDOr(null);
                }
            }
    
            return $this;
        }
}

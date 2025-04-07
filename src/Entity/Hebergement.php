<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Reservation_hebergement;

#[ORM\Entity]
class Hebergement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_hebergement;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $adresse;

    #[ORM\Column(type: "string")]
    private string $type;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    #[ORM\Column(type: "integer")]
    private int $prix;

    public function getId_hebergement()
    {
        return $this->id_hebergement;
    }

    public function setId_hebergement($value)
    {
        $this->id_hebergement = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($value)
    {
        $this->adresse = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($value)
    {
        $this->prix = $value;
    }

    #[ORM\OneToMany(mappedBy: "idH", targetEntity: Reservation_hebergement::class)]
    private Collection $reservation_hebergements;

        public function getReservation_hebergements(): Collection
        {
            return $this->reservation_hebergements;
        }
    
        public function addReservation_hebergement(Reservation_hebergement $reservation_hebergement): self
        {
            if (!$this->reservation_hebergements->contains($reservation_hebergement)) {
                $this->reservation_hebergements[] = $reservation_hebergement;
                $reservation_hebergement->setIdH($this);
            }
    
            return $this;
        }
    
        public function removeReservation_hebergement(Reservation_hebergement $reservation_hebergement): self
        {
            if ($this->reservation_hebergements->removeElement($reservation_hebergement)) {
                // set the owning side to null (unless already changed)
                if ($reservation_hebergement->getIdH() === $this) {
                    $reservation_hebergement->setIdH(null);
                }
            }
    
            return $this;
        }
}

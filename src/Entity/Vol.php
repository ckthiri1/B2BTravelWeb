<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reservation_voyage;

#[ORM\Entity]
class Vol
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "volID",type: "integer")]
    private int $volID;

    #[ORM\Column(name: "dateDepart",type: "datetime")]  
    private \DateTimeInterface $dateDepart;

    #[ORM\Column(name: "dateArrival",type: "datetime")]
    private \DateTimeInterface $dateArrival;

    #[ORM\Column(name: "airLine",type: "string", length: 255)]
    private string $airLine;

    #[ORM\Column(name: "flightNumber",type: "integer")]
    private int $flightNumber;

    #[ORM\Column(name: "dureeVol",type: "string", length: 255)]
    private string $dureeVol;

    #[ORM\Column(name: "prixVol",type: "integer")]
    private int $prixVol;

    #[ORM\Column(name: "typeVol",type: "string")]
    private string $typeVol;

    #[ORM\ManyToOne(targetEntity: Voyage::class)]
    #[ORM\JoinColumn(name: 'idVoyage', referencedColumnName: 'VID')]
    private Voyage $idVoyage;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id")]
    private User $user;
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    
    public function getUser(): User
    {
        return $this->user;
    }

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'NON_RESERVER';
    
    public const STATUS_NON_RESERVER = 'NON_RESERVER';
    public const STATUS_RESERVER = 'RESERVER';

    public function getVolID()
    {
        return $this->volID;
    }

    public function setVolID($value)
    {
        $this->volID = $value;
    }

    public function getDateDepart()
    {
        return $this->dateDepart;
    }

    public function setDateDepart($value)
    {
        $this->dateDepart = $value;
    }

    public function getDateArrival()
    {
        return $this->dateArrival;
    }

    public function setDateArrival($value)
    {
        $this->dateArrival = $value;
    }

    public function getAirLine()
    {
        return $this->airLine;
    }

    public function setAirLine($value)
    {
        $this->airLine = $value;
    }

    public function getFlightNumber()
    {
        return $this->flightNumber;
    }

    public function setFlightNumber($value)
    {
        $this->flightNumber = $value;
    }

    public function getDureeVol()
    {
        return $this->dureeVol;
    }

    public function setDureeVol($value)
    {
        $this->dureeVol = $value;
    }

    public function getPrixVol()
    {
        return $this->prixVol;
    }

    public function setPrixVol($value)
    {
        $this->prixVol = $value;
    }

    public function getTypeVol()
    {
        return $this->typeVol;
    }

    public function setTypeVol($value)
    {
        $this->typeVol = $value;
    }

    public function getIdVoyage() : Voyage
    {
        return $this->idVoyage;
    }

    public function setIdVoyage($value) : self
    {
        $this->idVoyage = $value;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_vol", targetEntity: Reservation_voyage::class)]
    private Collection $reservation_voyages;
}

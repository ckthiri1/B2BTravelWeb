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
    #[ORM\Column(type: "integer")]
    private int $volID;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDepart;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateArrival;

    #[ORM\Column(type: "string", length: 255)]
    private string $airLine;

    #[ORM\Column(type: "integer")]
    private int $flightNumber;

    #[ORM\Column(type: "string", length: 255)]
    private string $dureeVol;

    #[ORM\Column(type: "integer")]
    private int $prixVol;

    #[ORM\Column(type: "string")]
    private string $typeVol;

        #[ORM\ManyToOne(targetEntity: Voyage::class, inversedBy: "vols")]
    #[ORM\JoinColumn(name: 'idVoyage', referencedColumnName: 'VID', onDelete: 'CASCADE')]
    private Voyage $idVoyage;



    #[ORM\Column(type: "string")]
    private string $status;

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

    public function getIdVoyage()
    {
        return $this->idVoyage;
    }

    public function setIdVoyage($value)
    {
        $this->idVoyage = $value;
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

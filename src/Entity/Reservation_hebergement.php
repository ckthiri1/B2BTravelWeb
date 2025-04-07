<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Reservation_voyage;

#[ORM\Entity]
class Reservation_hebergement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_resH;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "integer")]
    private int $prix;

    #[ORM\Column(type: "string")]
    private string $status;

        #[ORM\ManyToOne(targetEntity: Hebergement::class, inversedBy: "reservation_hebergements")]
    #[ORM\JoinColumn(name: 'idH', referencedColumnName: 'id_hebergement', onDelete: 'CASCADE')]
    private Hebergement $idH;

        #[ORM\ManyToOne(targetEntity: Reservation_voyage::class, inversedBy: "reservation_hebergements")]
    #[ORM\JoinColumn(name: 'idResV', referencedColumnName: 'id_resV', onDelete: 'CASCADE')]
    private Reservation_voyage $idResV;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_fin;

    public function getId_resH()
    {
        return $this->id_resH;
    }

    public function setId_resH($value)
    {
        $this->id_resH = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($value)
    {
        $this->prix = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getIdH()
    {
        return $this->idH;
    }

    public function setIdH($value)
    {
        $this->idH = $value;
    }

    public function getIdResV()
    {
        return $this->idResV;
    }

    public function setIdResV($value)
    {
        $this->idResV = $value;
    }

    public function getDate_fin()
    {
        return $this->date_fin;
    }

    public function setDate_fin($value)
    {
        $this->date_fin = $value;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Organisateur;

#[ORM\Entity]
class Evennement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $IDE;

    #[ORM\Column(type: "string", length: 255)]
    private string $NomE;

    #[ORM\Column(type: "string", length: 255)]
    private string $Local;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $DateE;

    #[ORM\Column(type: "string", length: 255)]
    private string $DesE;

        #[ORM\ManyToOne(targetEntity: Organisateur::class, inversedBy: "evennements")]
    #[ORM\JoinColumn(name: 'IDOr', referencedColumnName: 'IDOr', onDelete: 'CASCADE')]
    private Organisateur $IDOr;

    #[ORM\Column(type: "string")]
    private string $event_type;

    public function getIDE()
    {
        return $this->IDE;
    }

    public function setIDE($value)
    {
        $this->IDE = $value;
    }

    public function getNomE()
    {
        return $this->NomE;
    }

    public function setNomE($value)
    {
        $this->NomE = $value;
    }

    public function getLocal()
    {
        return $this->Local;
    }

    public function setLocal($value)
    {
        $this->Local = $value;
    }

    public function getDateE()
    {
        return $this->DateE;
    }

    public function setDateE($value)
    {
        $this->DateE = $value;
    }

    public function getDesE()
    {
        return $this->DesE;
    }

    public function setDesE($value)
    {
        $this->DesE = $value;
    }

    public function getIDOr()
    {
        return $this->IDOr;
    }

    public function setIDOr($value)
    {
        $this->IDOr = $value;
    }

    public function getEvent_type()
    {
        return $this->event_type;
    }

    public function setEvent_type($value)
    {
        $this->event_type = $value;
    }
}

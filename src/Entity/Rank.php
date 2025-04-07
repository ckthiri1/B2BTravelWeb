<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Fidelite;

#[ORM\Entity]
#[ORM\Table(name: "rank")]
class Rank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IDRang", type: "integer")]
    private int $IDRang;

    #[ORM\Column(name: "NomRank", type: "string", length: 255)]
    private string $NomRank;

    #[ORM\Column(type: "integer")]
    private int $points;

    #[ORM\OneToMany(mappedBy: "IdRank", targetEntity: Fidelite::class)]
    private Collection $fidelites;

    public function __construct()
    {
        $this->fidelites = new ArrayCollection();
    }

    public function getIDRang(): int
    {
        return $this->IDRang;
    }

    public function setIDRang(int $value): void
    {
        $this->IDRang = $value;
    }

    public function getNomRank(): string
    {
        return $this->NomRank;
    }

    public function setNomRank(string $value): void
    {
        $this->NomRank = $value;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $value): void
    {
        $this->points = $value;
    }

    public function getFidelites(): Collection
    {
        return $this->fidelites;
    }

}

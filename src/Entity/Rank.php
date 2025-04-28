<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Fidelite;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: "rank")]
#[UniqueEntity(fields: ["NomRank"], message: "This rank name already exists.")]
class Rank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IDRang", type: "integer")]
    private int $IDRang;

    #[ORM\Column(name: "NomRank", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom du rank ne peut pas être vide.")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom du rank doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom du rank ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $NomRank = '';

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Les points ne peuvent pas être vides.")]
    #[Assert\Positive(message: "Les points doivent être un nombre positif.")]
    private ?int $points = 0;

    #[ORM\OneToMany(mappedBy: "IdRank", targetEntity: Fidelite::class)]
    private Collection $fidelites;

    public function __construct()
    {
        $this->fidelites = new ArrayCollection();
        $this->NomRank = '';
        $this->points = 0;
    }

    public function getIDRang(): int
    {
        return $this->IDRang;
    }

    public function setIDRang(int $value): void
    {
        $this->IDRang = $value;
    }

    public function getNomRank(): ?string
    {
        return $this->NomRank;
    }

    public function setNomRank(?string $value): void
    {
        $this->NomRank = $value ?? '';
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $value): void
    {
        $this->points = $value ?? 0;
    }

    public function getFidelites(): Collection
    {
        return $this->fidelites;
    }
}
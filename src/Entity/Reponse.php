<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
#[ORM\Table(name: "reponse")]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IDRep", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "DescriptionRep", type: Types::TEXT)]
    #[Assert\NotBlank(message: "La réponse ne peut pas être vide.")]
    private ?string $descriptionRep = null;

    #[ORM\Column(name: "DateRep", type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateRep = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: "reponses")]
    #[ORM\JoinColumn(name: "IDR", referencedColumnName: "IDR", nullable: false, onDelete: "CASCADE")]
    private ?Reclamation $reclamation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescriptionRep(): ?string
    {
        return $this->descriptionRep;
    }

    public function setDescriptionRep(string $descriptionRep): static
    {
        $this->descriptionRep = $descriptionRep;
        return $this;
    }

    public function getDateRep(): ?\DateTimeInterface
    {
        return $this->dateRep;
    }

    public function setDateRep(\DateTimeInterface $dateRep): static
    {
        $this->dateRep = $dateRep;
        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;
        return $this;
    }
}

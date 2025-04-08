<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IDR", type: "integer")]
    private ?int $id = null;
    
    #[ORM\Column(name: "Titre", length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 5,
        max: 100,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(name: "Description", type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(name: "DateR", type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\LessThanOrEqual(
        "today",
        message: "La date ne peut pas être dans le futur"
    )]
    private ?\DateTimeInterface $dateR = null;

    #[ORM\Column(name: "Status", length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['pending', 'resolved', 'closed'],
        message: "Le statut doit être 'pending', 'resolved' ou 'closed'"
    )]
    private ?string $status = 'pending';

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDateR(): ?\DateTimeInterface
    {
        return $this->dateR;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    // Setters avec validation intégrée
    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setDateR(?\DateTimeInterface $dateR): self
    {
        // Validation supplémentaire si nécessaire
        if ($dateR > new \DateTime()) {
            throw new \InvalidArgumentException("La date ne peut pas être dans le futur");
        }
        $this->dateR = $dateR;
        return $this;
    }

    public function setStatus(?string $status): self
    {
        $validStatuses = ['pending', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException(sprintf(
                "Le statut doit être l'un des suivants: %s",
                implode(', ', $validStatuses)
            ));
        }
        $this->status = $status;
        return $this;
    }

    // Méthodes utilitaires
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouvelle réclamation';
    }
}
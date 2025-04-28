<?php

namespace App\Entity;

use App\Repository\HebergementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HebergementRepository::class)]
#[ORM\Table(name: 'hebergement')]
class Hebergement
{
    public const TYPE_HOTEL = 'Hotel';
    public const TYPE_HOSTEL = 'Hostel';
    public const TYPE_MAISON = 'Maison';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_hebergement')] 
private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s\-\']+$/u",
        message: "Caractères spéciaux non autorisés"
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: "/^\d+\s+[a-zA-ZÀ-ÿ0-9\s\-\,'.]{5,}$/",
        message: "Format d'adresse invalide (ex: 12 Rue de Paris)"
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    #[Assert\Choice(
        choices: ['Hotel', 'Hostel', 'Maison'],
        message: "Type invalide"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column]
     #[Assert\NotBlank(message: "Le prix est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le prix doit être positif")]
    #[Assert\GreaterThanOrEqual(
        value: 10,
        message: "Le prix minimum est de 10 €"
    )]
    private ?int $prix = null;



    // Getters and setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    // Add similar getters/setters for other properties...
}
<?php

namespace App\Entity;

use App\Repository\EvennementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvennementRepository::class)]
#[ORM\Table(name: "evennement")]
class Evennement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IDE")]
    private ?int $id = null;

    #[ORM\Column(name: "NomE", length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'évènement est requis.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $NomE = null;

    #[ORM\Column(name: "Local", length: 255)]
    #[Assert\NotBlank(message: "La localisation est requise.")]
    #[Assert\Regex(
        pattern: '/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/',
        message: "La localisation doit être au format 'latitude, longitude'."
    )]
    private ?string $Local = null;

    #[ORM\Column(name: "DateE", type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de l'évènement est requise.")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date doit être valide.")]
    private ?\DateTimeInterface $DateE = null;

    #[ORM\Column(name: "DesE", type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(
        min: 10,
        max: 88888888,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $DesE = null;
    

    #[ORM\ManyToOne(targetEntity: Organisateur::class, inversedBy: 'evennements')]
    #[ORM\JoinColumn(name: "IDOr", referencedColumnName: "IDOr")]
    #[Assert\NotNull(message: "Veuillez sélectionner un organisateur.")]
    private ?Organisateur $IdOr = null;

    #[ORM\Column(name: "event_type", type: "string", length: 255, nullable: true)]
    private ?string $eventType = null;

    // Getters / Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomE(): ?string
    {
        return $this->NomE;
    }

    public function setNomE(string $NomE): static
    {
        $this->NomE = $NomE;
        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->Local;
    }

    public function setLocal(string $Local): static
    {
        $this->Local = $Local;
        return $this;
    }

    public function getDateE(): ?\DateTimeInterface
    {
        return $this->DateE;
    }

    public function setDateE(\DateTimeInterface $DateE): static
    {
        $this->DateE = $DateE;
        return $this;
    }

    public function getDesE(): ?string
    {
        return $this->DesE;
    }

    public function setDesE(string $DesE): static
    {
        $this->DesE = $DesE;
        return $this;
    }

    public function getIdOr(): ?Organisateur
    {
        return $this->IdOr;
    }

    public function setIdOr(?Organisateur $IdOr): static
    {
        $this->IdOr = $IdOr;
        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(?string $eventType): static
    {
        $this->eventType = $eventType;
        return $this;
    }

    public function getLatitude(): ?float
    {
        if (empty($this->Local)) {
            return null;
        }

        $coords = explode(',', $this->Local);
        return isset($coords[0]) ? (float)trim($coords[0]) : null;
    }

    public function getLongitude(): ?float
    {
        if (empty($this->Local)) {
            return null;
        }

        $coords = explode(',', $this->Local);
        return isset($coords[1]) ? (float)trim($coords[1]) : null;
    }
}

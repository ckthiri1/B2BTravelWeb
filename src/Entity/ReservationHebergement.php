<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\ReservationHebergementRepository")]
#[ORM\Table(name: "reservation_hebergement")]
class ReservationHebergement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_resH", type: "integer")]
    private ?int $idResH = null;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "integer")]
#[Assert\Positive(message: "Le prix doit être positif")]
private int $prix;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['EnAttente', 'Resolue'],
        message: "Statut invalide. Choisissez entre EnAttente ou Resolue"
    )]
    private ?string $status = null;

    #[ORM\ManyToOne(targetEntity: Hebergement::class)]
    #[ORM\JoinColumn(
        name: "idH", 
        referencedColumnName: "id_hebergement", // Doit correspondre au nom de colonne dans Hebergement
        nullable: true
    )]
    private ?Hebergement $hebergement = null;

    #[ORM\Column(name: "date_fin", type: "date")]
    private \DateTimeInterface $dateFin;

    // Getters and setters

    public function getIdResH(): ?int
    {
        return $this->idResH;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, ['EnAttente', 'Resolue'])) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
        return $this;
    }

    public function getHebergement(): ?Hebergement
    {
        return $this->hebergement;
    }

    public function setHebergement(?Hebergement $hebergement): self
    {
        $this->hebergement = $hebergement;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }
    public function __construct()
{
    $this->date = new \DateTime();
    $this->dateFin = (new \DateTime())->modify('+1 day');
    $this->prix = 0;
}
}
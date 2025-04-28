<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Vol;
use Doctrine\Common\Collections\Collection;
use App\Entity\ReservationHebergement;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Reservation_voyage
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_resV", type: "integer")]
    private int $id_resV;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reservation_voyages")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private User $id_user;

    #[ORM\ManyToOne(targetEntity: Vol::class, inversedBy: "reservation_voyages")]
    #[ORM\JoinColumn(name: 'id_vol', referencedColumnName: 'volID', onDelete: 'CASCADE')]
    private Vol $id_vol;

    #[ORM\Column(name: "place", type: "integer")]
    private int $place;

    #[ORM\Column(name: "prixTotal", type: "float")]
    private float $prixTotal;


    #[ORM\Column(length: 32, unique: true)]
    private ?string $reservationCode = null;


    #[ORM\Column(length: 20, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $paymentStatus = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $paymentDate = null;



    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }


    public function getReservationCode(): ?string
    {
        return $this->reservationCode;
    }
    public function setReservationCode(string $reservationCode): static
    {
        $this->reservationCode = $reservationCode;
        return $this;
    }

    public function getId_resV()
    {
        return $this->id_resV;
    }

    public function setId_resV($value)
    {
        $this->id_resV = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getId_vol()
    {
        return $this->id_vol;
    }

    public function setId_vol($value)
    {
        $this->id_vol = $value;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function setPlace($value)
    {
        $this->place = $value;
    }

    public function getPrixTotal()
    {
        return $this->prixTotal;
    }

    public function setPrixTotal($value)
    {
        $this->prixTotal = $value;
    }
    #[ORM\OneToMany(targetEntity: ReservationHebergement::class, mappedBy: "reservationVoyage")]
    private Collection $reservationHebergements;

    public function __construct()
    {
        $this->reservationHebergements = new ArrayCollection();
    }

    // Add getter and setter for the collection
    public function getReservationHebergements(): Collection
    {
        return $this->reservationHebergements;
    }

    public function addReservationHebergement(ReservationHebergement $reservationHebergement): self
    {
        if (!$this->reservationHebergements->contains($reservationHebergement)) {
            $this->reservationHebergements[] = $reservationHebergement;
            $reservationHebergement->setReservationVoyage($this);
        }
        return $this;
    }

    public function removeReservationHebergement(ReservationHebergement $reservationHebergement): self
    {
        if ($this->reservationHebergements->removeElement($reservationHebergement)) {
            if ($reservationHebergement->getReservationVoyage() === $this) {
                $reservationHebergement->setReservationVoyage(null);
            }
        }
        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Vol;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reservation_hebergement;

#[ORM\Entity]
class Reservation_voyage
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_resV;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reservation_voyages")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private User $id_user;

        #[ORM\ManyToOne(targetEntity: Vol::class, inversedBy: "reservation_voyages")]
    #[ORM\JoinColumn(name: 'id_vol', referencedColumnName: 'volID', onDelete: 'CASCADE')]
    private Vol $id_vol;

    #[ORM\Column(type: "integer")]
    private int $place;

    #[ORM\Column(type: "float")]
    private float $prixTotal;

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

    #[ORM\OneToMany(mappedBy: "idResV", targetEntity: Reservation_hebergement::class)]
    private Collection $reservation_hebergements;
}

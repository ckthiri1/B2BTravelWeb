<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "fidelite")]
class Fidelite
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "id_f", type: "integer")]
    private int $IdF;

    #[ORM\Column(type: "integer")]
    private int $points;

    #[ORM\Column(type: "float")]
    private float $remise;

        #[ORM\ManyToOne(targetEntity: Rank::class, inversedBy: "fidelites")]
    #[ORM\JoinColumn(name: 'IdRank', referencedColumnName: 'IDRang', onDelete: 'CASCADE')]
    private ?Rank $IdRank ;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "fidelites")]
    #[ORM\JoinColumn(name: 'idUser', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private User $idUser;

    public function getIdF()
    {
        return $this->IdF;
    }

    public function setIdF($value)
    {
        $this->IdF = $value;
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function setPoints($value)
    {
        $this->points = $value;
    }

    public function getRemise()
    {
        return $this->remise;
    }

    public function setRemise($value)
    {
        $this->remise = $value;
    }

    public function getIdRank()
    {
        return $this->IdRank;
    }

    public function setIdRank($value)
    {
        $this->IdRank = $value;
    }

    public function getIdUser()
    {
        return $this->idUser;
    }

    public function setIdUser($value)
    {
        $this->idUser = $value;
    }
}

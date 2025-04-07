<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Reclamation;

#[ORM\Entity]
class Reponse
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $IDRep;

    #[ORM\Column(type: "string", length: 255)]
    private string $DescriptionRep;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $DateRep;

        #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: "reponses")]
    #[ORM\JoinColumn(name: 'IDR', referencedColumnName: 'IDR', onDelete: 'CASCADE')]
    private Reclamation $IDR;

    public function getIDRep()
    {
        return $this->IDRep;
    }

    public function setIDRep($value)
    {
        $this->IDRep = $value;
    }

    public function getDescriptionRep()
    {
        return $this->DescriptionRep;
    }

    public function setDescriptionRep($value)
    {
        $this->DescriptionRep = $value;
    }

    public function getDateRep()
    {
        return $this->DateRep;
    }

    public function setDateRep($value)
    {
        $this->DateRep = $value;
    }

    public function getIDR()
    {
        return $this->IDR;
    }

    public function setIDR($value)
    {
        $this->IDR = $value;
    }
}

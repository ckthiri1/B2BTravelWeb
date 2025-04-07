<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reponse;

#[ORM\Entity]
class Reclamation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $IDR;

    #[ORM\Column(type: "string", length: 255)]
    private string $Titre;

    #[ORM\Column(type: "string", length: 255)]
    private string $Description;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $DateR;

    #[ORM\Column(type: "string", length: 255)]
    private string $Status;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private User $id_user;

    public function getIDR()
    {
        return $this->IDR;
    }

    public function setIDR($value)
    {
        $this->IDR = $value;
    }

    public function getTitre()
    {
        return $this->Titre;
    }

    public function setTitre($value)
    {
        $this->Titre = $value;
    }

    public function getDescription()
    {
        return $this->Description;
    }

    public function setDescription($value)
    {
        $this->Description = $value;
    }

    public function getDateR()
    {
        return $this->DateR;
    }

    public function setDateR($value)
    {
        $this->DateR = $value;
    }

    public function getStatus()
    {
        return $this->Status;
    }

    public function setStatus($value)
    {
        $this->Status = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    #[ORM\OneToMany(mappedBy: "IDR", targetEntity: Reponse::class)]
    private Collection $reponses;

        public function getReponses(): Collection
        {
            return $this->reponses;
        }
    
        public function addReponse(Reponse $reponse): self
        {
            if (!$this->reponses->contains($reponse)) {
                $this->reponses[] = $reponse;
                $reponse->setIDR($this);
            }
    
            return $this;
        }
    
        public function removeReponse(Reponse $reponse): self
        {
            if ($this->reponses->removeElement($reponse)) {
                // set the owning side to null (unless already changed)
                if ($reponse->getIDR() === $this) {
                    $reponse->setIDR(null);
                }
            }
    
            return $this;
        }
}

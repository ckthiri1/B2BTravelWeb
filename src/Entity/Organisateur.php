<?php

namespace App\Entity;

use App\Repository\OrganisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Evennement;
use App\Service\TwilioService;

#[ORM\Entity(repositoryClass: OrganisateurRepository::class)]
class Organisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IDOr")]
    private ?int $idor = null;

    #[ORM\Column(name: "NomOr", type: "string", length: 255)]
    private ?string $nomOr = null;

    #[ORM\Column(name: "Contact", type: "string", length: 255)]
    private ?string $contact = null;

    #[ORM\OneToMany(mappedBy: 'IdOr', targetEntity: Evennement::class)]
    private Collection $evennements;

    public function getId(): ?int
{
    return $this->idor;
}


    public function __construct()
    {
        $this->evennements = new ArrayCollection();
    }

    public function getIdor(): ?int
    {
        return $this->idor;
    }

    public function getNomOr(): ?string
    {
        return $this->nomOr;
    }

    public function setNomOr(string $nomOr): static
    {
        $this->nomOr = $nomOr;
        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): static
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return Collection<int, Evennement>
     */
    public function getEvennements(): Collection
    {
        return $this->evennements;
    }

    public function addEvennement(Evennement $evennement): static
    {
        if (!$this->evennements->contains($evennement)) {
            $this->evennements[] = $evennement;
            $evennement->setIdOr($this);
        }

        return $this;
    }

    public function removeEvennement(Evennement $evennement): static
    {
        if ($this->evennements->removeElement($evennement)) {
            if ($evennement->getIdOr() === $this) {
                $evennement->setIdOr(null);
            }
        }

        return $this;
    }
}

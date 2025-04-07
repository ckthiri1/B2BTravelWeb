<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Vol;

#[ORM\Entity]
class User
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $user_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $pwd;

    #[ORM\Column(type: "integer")]
    private int $nbrVoyage;

    #[ORM\Column(type: "string")]
    private string $role;

    #[ORM\Column(type: "string", length: 255)]
    private string $hash;

    #[ORM\Column(type: "string", length: 16)]
    private string $salt;

    #[ORM\Column(type: "string", length: 255)]
    private string $image_url;

    #[ORM\Column(type: "string", length: 255)]
    private string $reset_token;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $token_expiry;

    #[ORM\Column(type: "text")]
    private string $face_embedding;

    #[ORM\Column(type: "string", length: 65535)]
    private string $face_image;

    #[ORM\Column(type: "text")]
    private string $voice_features;

    #[ORM\Column(type: "string", length: 255)]
    private string $remember_token;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $remember_expiry;

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPwd()
    {
        return $this->pwd;
    }

    public function setPwd($value)
    {
        $this->pwd = $value;
    }

    public function getNbrVoyage()
    {
        return $this->nbrVoyage;
    }

    public function setNbrVoyage($value)
    {
        $this->nbrVoyage = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($value)
    {
        $this->hash = $value;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($value)
    {
        $this->salt = $value;
    }

    public function getImage_url()
    {
        return $this->image_url;
    }

    public function setImage_url($value)
    {
        $this->image_url = $value;
    }

    public function getReset_token()
    {
        return $this->reset_token;
    }

    public function setReset_token($value)
    {
        $this->reset_token = $value;
    }

    public function getToken_expiry()
    {
        return $this->token_expiry;
    }

    public function setToken_expiry($value)
    {
        $this->token_expiry = $value;
    }

    public function getFace_embedding()
    {
        return $this->face_embedding;
    }

    public function setFace_embedding($value)
    {
        $this->face_embedding = $value;
    }

    public function getFace_image()
    {
        return $this->face_image;
    }

    public function setFace_image($value)
    {
        $this->face_image = $value;
    }

    public function getVoice_features()
    {
        return $this->voice_features;
    }

    public function setVoice_features($value)
    {
        $this->voice_features = $value;
    }

    public function getRemember_token()
    {
        return $this->remember_token;
    }

    public function setRemember_token($value)
    {
        $this->remember_token = $value;
    }

    public function getRemember_expiry()
    {
        return $this->remember_expiry;
    }

    public function setRemember_expiry($value)
    {
        $this->remember_expiry = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Reclamation::class)]
    private Collection $reclamations;

        public function getReclamations(): Collection
        {
            return $this->reclamations;
        }
    
        public function addReclamation(Reclamation $reclamation): self
        {
            if (!$this->reclamations->contains($reclamation)) {
                $this->reclamations[] = $reclamation;
                $reclamation->setId_user($this);
            }
    
            return $this;
        }
    
        public function removeReclamation(Reclamation $reclamation): self
        {
            if ($this->reclamations->removeElement($reclamation)) {
                // set the owning side to null (unless already changed)
                if ($reclamation->getId_user() === $this) {
                    $reclamation->setId_user(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "idUser", targetEntity: Fidelite::class)]
    private Collection $fidelites;

        public function getFidelites(): Collection
        {
            return $this->fidelites;
        }
    
        public function addFidelite(Fidelite $fidelite): self
        {
            if (!$this->fidelites->contains($fidelite)) {
                $this->fidelites[] = $fidelite;
                $fidelite->setIdUser($this);
            }
    
            return $this;
        }
    
        public function removeFidelite(Fidelite $fidelite): self
        {
            if ($this->fidelites->removeElement($fidelite)) {
                // set the owning side to null (unless already changed)
                if ($fidelite->getIdUser() === $this) {
                    $fidelite->setIdUser(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Reservation_voyage::class)]
    private Collection $reservation_voyages;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Vol::class)]
    private Collection $vols;

        public function getVols(): Collection
        {
            return $this->vols;
        }
    
        public function incrementNbrVoyage(): self
        {
            $this->nbrVoyage++;
            return $this;
        }
}

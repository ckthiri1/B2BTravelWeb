<?php
// src/Entity/User.php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'user', options: [
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci'
])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'This email is already registered.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_id', type: Types::INTEGER)]
    private ?int $userId = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Last name is required')]
    #[Assert\Type(type: 'string', message: 'Last name must be a string')]
    private string $nom;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'first name is required')]
    #[Assert\Type(type: 'string', message: 'First name must be a string')]
    private string $prenom;

    public function __construct()
    {
        $this->nom = '';
        $this->prenom = '';
        $this->email = '';
        $this->pwd = '';
        $this->role = 'user';
        $this->hash = '';
        $this->image_url = 'default.png';
        $this->reclamations = new ArrayCollection();
    }

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'The email "{{ value }}" is not a valid email.')]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $pwd;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $nbrVoyage = null;

    #[ORM\Column(
        type: Types::STRING,
        columnDefinition: "ENUM('user', 'admin') NOT NULL"
    )]
    private string $role;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $hash;

    #[ORM\Column(
        type: Types::BINARY,
        columnDefinition: "BINARY(16) NOT NULL"
    )]
    private $salt;

    #[ORM\Column(name: 'image_url', type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Image URL is required')]
    private string $image_url;

    #[ORM\Column(name: 'reset_token', type: Types::STRING, length: 255, nullable: true)]
    private ?string $reset_token = null;

    #[ORM\Column(name: 'token_expiry', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $token_expiry = null;

    #[ORM\Column(name: 'face_embedding', type: Types::TEXT, nullable: true)]
    private ?string $face_embedding = null;

    #[ORM\Column(
        name: 'face_image',
        type: Types::BLOB,
        nullable: true,
        columnDefinition: "BLOB"
    )]
    private $face_image = null;

    #[ORM\Column(name: 'voice_features', type: Types::TEXT, nullable: true)]
    private ?string $voice_features = null;

    #[ORM\Column(name: 'remember_token', type: Types::STRING, length: 255, nullable: true)]
    private ?string $remember_token = null;

    #[ORM\Column(name: 'remember_expiry', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $remember_expiry = null;

    #[ORM\Column(name: 'is_verified', type: Types::BOOLEAN)]
    private bool $isVerified = false;

    #[ORM\Column(name: 'google_id', type: Types::STRING, length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(name: 'is_banned', type: Types::BOOLEAN)]
private bool $isBanned = false;

#[ORM\Column(name: 'banned_reason', type: Types::TEXT, nullable: true)]
private ?string $bannedReason = null;

#[ORM\Column(name: 'banned_until', type: Types::DATETIME_MUTABLE, nullable: true)]
private ?\DateTimeInterface $bannedUntil = null;

// Add these getters and setters
public function isBanned(): bool
{
    return $this->isBanned;
}

public function setIsBanned(bool $isBanned): self
{
    $this->isBanned = $isBanned;
    return $this;
}

public function getBannedReason(): ?string
{
    return $this->bannedReason;
}

public function setBannedReason(?string $bannedReason): self
{
    $this->bannedReason = $bannedReason;
    return $this;
}

public function getBannedUntil(): ?\DateTimeInterface
{
    return $this->bannedUntil;
}

public function setBannedUntil(?\DateTimeInterface $bannedUntil): self
{
    $this->bannedUntil = $bannedUntil;
    return $this;
}

    // Add getter and setter
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;
        return $this;
    }

    // Getters and Setters
    public function getUserId(): ?int   
    {
        return $this->userId;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPwd(): string
    {
        return $this->pwd;
    }

    public function setPwd(string $pwd): self
    {
        $this->pwd = $pwd;
        return $this;
    }

    public function getNbrVoyage(): ?int
    {
        return $this->nbrVoyage;
    }

    public function setNbrVoyage(?int $nbrVoyage): self
    {
        $this->nbrVoyage = $nbrVoyage;
        return $this;
    }

    public function getRoles(): array
{
    $role = $this->getRole(); // Use the getter instead of direct property access
    if (!str_starts_with($role, 'ROLE_')) {
        $role = 'ROLE_' . strtoupper($role);
    }
    return [$role];
}

    public function getRole(): string
{
    return $this->role;
}
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }



    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;
        return $this;
    }


    public function getImageUrl(): string
    {
        return $this->image_url;
    }

   
public function setImageUrl(string $image_url): self
{
    $this->image_url = $image_url;
    return $this;
}

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;
        return $this;
    }

    public function getTokenExpiry(): ?\DateTimeInterface
    {
        return $this->token_expiry;
    }

    public function setTokenExpiry(?\DateTimeInterface $token_expiry): self
    {
        $this->token_expiry = $token_expiry;
        return $this;
    }

    public function getFaceEmbedding(): ?string
    {
        return $this->face_embedding;
    }

    public function setFaceEmbedding(?string $face_embedding): self
    {
        $this->face_embedding = $face_embedding;
        return $this;
    }

    public function getFaceImage()
    {
        return is_resource($this->face_image) ? \stream_get_contents($this->face_image) : $this->face_image;
    }

    public function setFaceImage($face_image): self
    {
        $this->face_image = $face_image;
        return $this;
    }

    public function getVoiceFeatures(): ?string
    {
        return $this->voice_features;
    }

    public function setVoiceFeatures(?string $voice_features): self
    {
        $this->voice_features = $voice_features;
        return $this;
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken(?string $remember_token): self
    {
        $this->remember_token = $remember_token;
        return $this;
    }

    public function getRememberExpiry(): ?\DateTimeInterface
    {
        return $this->remember_expiry;
    }

    public function setRememberExpiry(?\DateTimeInterface $remember_expiry): self
    {
        $this->remember_expiry = $remember_expiry;
        return $this;
    }

    // UserInterface Methods

    public function eraseCredentials(): void
    {
        // Clear temporary sensitive data
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }


    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function isVerified(): bool
    {
    return $this->isVerified;
    }

public function setIsVerified(bool $isVerified): self
    {
    $this->isVerified = $isVerified;
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

        #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: "id_user")]
        private Collection $reclamations;
        
        // Add getter and methods to manage the collection
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
    
}
<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Id]
    #[ORM\Column(length: 90, unique: true)]
    private ?string $idUser = null;

    #[ORM\Column(length: 55)]
    private ?string $firstname = null;

    #[ORM\Column(length: 55)]
    private ?string $lastname = null;

    #[ORM\Column(length: 80)]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(length: 90)]
    private ?string $encrypte = null;

    #[ORM\Column(length: 55, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(type: 'json')]
    private ?array $roles = [];

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $isactive = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateBirth = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToOne(mappedBy: 'User_idUser', cascade: ['persist', 'remove'])]
    private ?Artist $artist = null;


    public function getIdUser(): ?string
    {
        return $this->idUser;
    }

    public function setIdUser(string $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->encrypte;
    }

    public function setPassword(string $encrypte): static
    {
        $this->encrypte = $encrypte;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }


    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getIsActive(): ?string
    {
        return $this->isactive;
    }
    public function setIsActive(?string $isactive): static
    {
        $this->isactive = $isactive;
        return $this;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function getRoles(): array
    {
        // Retourne un tableau de chaînes de caractères représentant les rôles de l'utilisateur
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        // Utilise array_unique pour supprimer les doublons éventuels dans le tableau des rôles
        $this->roles = array_unique($roles);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getDateBirth(): ?\DateTimeInterface
    {
        return $this->dateBirth;
    }

    public function setDateBirth(?\DateTimeInterface $dateBirth): static
    {
        $this->dateBirth = $dateBirth;

        return $this;
    }
    /**
     * 
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }



    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(Artist $artist): static
    {

        if ($artist->getUserIdUser() !== $this) {
            $artist->setUserIdUser($this);
        }

        $this->artist = $artist;

        return $this;
    }

    public function registerSerializer()
    {
        $date = $this->getDateBirth() ? $this->getDateBirth()->format('Y-m-d') : null;
        $sexe = $this->getSexe() === '1' ? 'Homme' : 'Femme';
        return [
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'tel' => $this->getTel(),
            'sexe' =>  $sexe,
            'dateBirth' => $date,
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updateAt' => $this->getUpdateAt() ? $this->getUpdateAt()->format('Y-m-d H:i:s') : null,
        ];
    }


    public function loginSerializer(bool $incArtist = false)
    {
        $date = $this->getDateBirth() ? $this->getDateBirth()->format('d-m-Y') : null;
        $artistData = $incArtist && $this->getArtist() !== null ? $this->getArtist() : [];

        $sexe = $this->getSexe() == '0' ? 'Femme' : 'Homme';

        return [
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'tel' => $this->getTel(),
            'sexe' =>  $sexe,
            'artist' => $artistData,
            'dateBirth' => $date,
            'createdAt' => $this->getCreatedAt(),
        ];
    }
}

<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;


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

    #[ORM\Column(length: 80, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 90)]
    private ?string $encrypte = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $dateBirth = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $sexe = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $isActive = true;


    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    // Getter pour isActive
    public function isActive(): bool
    {
        return $this->isActive;
    }


    // Getters et setters pour dateBirth
    public function getDateBirth(): ?\DateTimeInterface
    {
        return $this->dateBirth;
    }

    public function setDateBirth(?\DateTimeInterface $dateBirth): static
    {
        $this->dateBirth = $dateBirth;

        return $this;
    }

    // Getters et setters pour sexe
    public function getSexe(): ?int
    {
        return $this->sexe;
    }

    public function setSexe(?int $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }


    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToOne(mappedBy: 'User_idUser', cascade: ['persist', 'remove'])]
    private ?Artist $artist = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?string
    {
        return $this->idUser;
    }

    public function setIdUser(string $idUser): static
    {
        $this->idUser = $idUser;

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

    public function getUsername(): ?string
    {
        return $this->email;
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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

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

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function getFormattedSexe(): ?string
    {
        $sexe = $this->getSexe();

        if ($sexe === 0) {
            return "Femme";
        } elseif ($sexe === 1) {
            return "Homme";
        } else {
            return null;
        }
    }
    public function setArtist(Artist $artist): static
    {
        // set the owning side of the relation if necessary
        if ($artist->getUserIdUser() !== $this) {
            $artist->setUserIdUser($this);
        }

        $this->artist = $artist;

        return $this;
    }



    public function getRoles(): array
    {

        return ['PUBLIC_ACCESS'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function serializer()
    {
        return [
            "id" => $this->getId(),
            "idUser" => $this->getIdUser(),
            "firstname" => $this->getFirstName(),
            "lastname" => $this->getLastName(),
            "email" => $this->getEmail(),
            "tel" => $this->getTel(),
            "sexe" => $this->getSexe() === "0" ? "Femme" : ($this->getSexe() === "1" ? "Homme" : null),
            "createAt" => $this->getCreatedAt(),
            "artist" => $this->getArtist() ?  $this->getArtist()->serializer() : [],
        ];
    }
}

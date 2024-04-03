<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Id]
    #[ORM\Column(length: 90, unique:true)]
    private ?string $idUser = null;

    #[ORM\Column(length: 55)]
    private ?string $name = null;

    #[ORM\Column(length: 55)] 
    private ?string $firstname  = null;

    #[ORM\Column(length: 55)]
    private ?string $lastname  = null;

    #[ORM\Column(length: 80, unique:true)]
    private ?string $email  = null;

    #[ORM\Column(length: 90)]
    private ?string $encrypte  = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tel  = null;


    #[ORM\Column(type: 'date')] 
    private ?\DateTimeInterface $dateBirth  = null;

    #[ORM\Column(length: 20)] 
    private ?string $sexe  = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }


        public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

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
    public function getDateBirth(): \DateTimeInterface
    {
        return $this->dateBirth;
    }
    
    public function setDateBirth(\DateTimeInterface $dateBirth): self
    {
        $this->dateBirth = $dateBirth;
        return $this;
    }
    


    public function getArtist(): ?Artist
    {
        return $this->artist;
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

    public function getSexe(): string
    {
        return $this->sexe;
    } 

    public function setSexe(string $sexe): self
    {
        if (!in_array($sexe, ['Homme', 'Femme', 'Autre'])) {
            throw new \InvalidArgumentException("Invalid sexe value");
        }

        $this->sexe = $sexe;

        return $this;
    }
    public function getRoles(): array {
        $roles = ['ROLE_USER']; //default roles
    
        if ($this->getArtist() !== null) {
            $roles[] = 'ROLE_ARTISTE';
        }
    
        return $roles;
    }
    
    public function eraseCredentials(): void{

    }

    public function getUserIdentifier(): string{
        return "";
    }


    public function serializer()
    {
        return [
        "id" => $this->getId(),
        "idUser" => $this->getIdUser(),
        "name" => $this->getName(),
        "firstName" => $this->getFirstName(),
        "lastName" => $this->getLastName(),
        "email" => $this->getEmail(),
        "tel" => $this->getTel(),
        "dateBirth" => $this->getDateBirth()->format('d/m/Y'),
        "createAt" => $this->getCreateAt()->format('d/m/Y'), 
        "artist" => $this->getArtist() ?  $this->getArtist()->serializer() : [],
         ];
    }
}
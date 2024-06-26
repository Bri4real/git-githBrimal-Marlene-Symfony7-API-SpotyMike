<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'artist', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User_idUser = null;

    #[ORM\Column(length: 90)]
    private ?string $fullname = null;


    #[ORM\Column(length: 90)]
    private ?string $isactive = null;

    //createdAt
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $avatar = null;

    //followers string
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $followers = '0';


    #[ORM\ManyToMany(targetEntity: Song::class, mappedBy: 'Artist_idUser')]
    private Collection $songs;

    #[ORM\OneToMany(targetEntity: Album::class, mappedBy: 'artist_User_idUser')]
    private Collection $albums;

    #[ORM\OneToMany(targetEntity: LabelHasArtist::class, mappedBy: 'idArtist')]
    private Collection $labelHasArtist;

    #[ORM\ManyToMany(targetEntity: Featuring::class, mappedBy: 'idArtist')]
    private Collection $featurings;



    public function __construct()
    {
        $this->songs = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->labelHasArtist = new ArrayCollection();
        $this->featurings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdUser(): ?User
    {
        return $this->User_idUser;
    }

    public function setUserIdUser(User $User_idUser): static
    {
        $this->User_idUser = $User_idUser;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFollowers(): ?string
    {
        return $this->followers;
    }

    public function setFollowers(?string $followers): static
    {
        $this->followers = $followers;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    /**
     * @return Collection<int, Album>
     */
    public function getAlbums(): Collection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): static
    {
        if (!$this->albums->contains($album)) {
            $this->albums->add($album);
            $album->setArtistUserIdUser($this);
        }

        return $this;
    }

    public function removeAlbum(Album $album): static
    {
        if ($this->albums->removeElement($album)) {
            if ($album->getArtistUserIdUser() === $this) {
                $album->setArtistUserIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LabelHasArtist>
     */
    public function getLabelHasArtist(): Collection
    {
        return $this->labelHasArtist;
    }

    public function addLabelHasArtist(LabelHasArtist $labelHasArtist): static
    {
        if (!$this->labelHasArtist->contains($labelHasArtist)) {
            $this->labelHasArtist->add($labelHasArtist);
            $labelHasArtist->setIdArtist($this);
        }

        return $this;
    }

    public function removeLabelHasArtist(LabelHasArtist $labelHasArtist): static
    {
        if ($this->labelHasArtist->removeElement($labelHasArtist)) {
            if ($labelHasArtist->getIdArtist() === $this) {
                $labelHasArtist->setIdArtist(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Featuring>
     */
    public function getFeaturings(): Collection
    {
        return $this->featurings;
    }

    public function addFeaturing(Featuring $featuring): static
    {
        if (!$this->featurings->contains($featuring)) {
            $this->featurings->add($featuring);
            $featuring->addIdArtist($this);
        }

        return $this;
    }

    public function removeFeaturing(Featuring $featuring): static
    {
        if ($this->featurings->removeElement($featuring)) {
            $featuring->removeIdArtist($this);
        }

        return $this;
    }


    public function getAlbumArtist()
    {
        $date = $this->getUserIdUser()->getDateBirth() ? $this->getUserIdUser()->getDateBirth()->format('d-m-Y') : null;
        $createdAt = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d') : null;
        $sexe = $this->getUserIdUser()->getSexe() == '0' ? 'Femme' : 'Homme';
        return [
            'firstname' => $this->getUserIdUser()->getFirstname(),
            'lastname' => $this->getUserIdUser()->getLastname(),
            'fullname' => $this->getFullname(),
            'avatar' => $this->getAvatar(),
            'follower' => $this->getFollowers(),
            'cover' => $this->getAvatar(),
            'sexe' => $sexe,
            'dateBirth' => $date,
            'Artist.createdAt' => $createdAt,
        ];
    }


    public function getAllArtistsInfo()
    {
        $date = $this->getUserIdUser()->getDateBirth() ? $this->getUserIdUser()->getDateBirth()->format('d-m-Y') : null;
        $feat = [];
        foreach ($this->getFeaturings() as $feat) {
            $feat[] = $feat->featuringSerializer();
        }


        $createdAt = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d') : null;
        $sexe = $this->getUserIdUser()->getSexe() == '0' ? 'Femme' : 'Homme';
        return [
            'firstname' => $this->getUserIdUser()->getFirstname(),
            'lastname' => $this->getUserIdUser()->getLastname(),
            'fullname' => $this->getFullname(),
            'avatar' => $this->getAvatar(),
            'sexe' => $sexe,
            'dateBirth' => $date,
            'Artist.CreatedAt' => $createdAt,
            'albums' => $this->albums->map(function ($album) {
                return $album->albumSerializer();
            }),
        ];
    }
    public function getArtistInfo()
    {
        $date = $this->getUserIdUser()->getDateBirth() ? $this->getUserIdUser()->getDateBirth()->format('d-m-Y') : null;
        $feat = [];
        foreach ($this->getFeaturings() as $feat) {
            $feat[] = $feat->featuringSerializer();
        }
        $createdAt = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d') : null;
        $sexe = $this->getUserIdUser()->getSexe() == '0' ? 'Femme' : 'Homme';
        return [
            'firstname' => $this->getUserIdUser()->getFirstname(),
            'lastname' => $this->getUserIdUser()->getLastname(),
            'fullname' => $this->getFullname(),
            'avatar' => $this->getAvatar(),
            'follower' => $this->getFollowers(),
            'sexe' => $sexe,
            'dateBirth' => $date,
            'Artist.CreatedAt' => $createdAt,
            'featuring' => $feat,
            'albums' => $this->albums->map(function ($album) {
                return $album->albumSerializer();
            }),
        ];
    }
}

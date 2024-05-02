<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 90)]
    private ?string $idAlbum = null;

    // id nom categ label cover year et created at

    #[ORM\Column(length: 95)]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    private ?string $categ = null;

    #[ORM\Column(length: 125)]
    private ?string $cover = null;

    //add visibility
    #[ORM\Column]
    private ?string $visibility = '0';

    #[ORM\Column(length: 90)]
    private ?string $name = null;

    /**
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;


    #[ORM\ManyToOne(inversedBy: 'albums')]
    private ?Artist $artist_User_idUser = null;

    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'album')]
    private Collection $song_idSong;

    public function __construct()
    {
        $this->song_idSong = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAlbum(): ?string
    {
        return $this->idAlbum;
    }

    public function setIdAlbum(?string $idAlbum = null): self
    {
        if ($idAlbum !== null) {
            $this->idAlbum = $idAlbum;
        } else {
            $uuid = Uuid::v4();
            $this->idAlbum = 'spotimike:album:' . $uuid;
        }
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->nom;
    }

    public function setTitle(string $nom): static
    {
        $this->nom = $nom;

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

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCateg(): ?string
    {
        return $this->categ;
    }

    public function setCateg(string $categ): static
    {
        $this->categ = $categ;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getYear(): ?\DateTimeInterface
    {
        return $this->year;
    }

    public function setYear(\DateTimeInterface $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getArtistUserIdUser(): ?Artist
    {
        return $this->artist_User_idUser;
    }

    public function setArtistUserIdUser(?Artist $artist_User_idUser): static
    {
        $this->artist_User_idUser = $artist_User_idUser;

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongIdSong(): Collection
    {
        return $this->song_idSong;
    }

    public function addSongIdSong(Song $songIdSong): static
    {
        if (!$this->song_idSong->contains($songIdSong)) {
            $this->song_idSong->add($songIdSong);
            $songIdSong->setAlbum($this);
        }

        return $this;
    }

    public function removeSongIdSong(Song $songIdSong): static
    {
        if ($this->song_idSong->removeElement($songIdSong)) {
            if ($songIdSong->getAlbum() === $this) {
                $songIdSong->setAlbum(null);
            }
        }

        return $this;
    }

    public function getAllAlbums()
    {
        $songs = [];
        foreach ($this->getSongIdSong() as $song) {
            //   $songs[] = $song->songSerializerForAlbum();
        }

        $artist = $this->getArtistUserIdUser();
        $year = $this->getCreatedAt();
        $formatYear = $year ? $year->format('Y') : null;
        $label = $this->getArtistLabel($artist, $formatYear);
        $createdAt = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d') : null;


        return [
            'id' => strval($this->getId()),
            'nom' => $this->getTitle(),
            'categ' => $this->getCateg(),
            'label' => $label,
            'cover' => $this->getCover(),
            'year' => $formatYear,
            'createdAt' => $createdAt,
            'songs' => $songs,
            'artist' => $artist->getAlbumArtist(),

        ];
    }
    public function getAlbum()
    {
        $songs = $this->serializeSongs();
        $artist = $this->getArtistUserIdUser();
        $formatYear = $this->formatYear();
        $label = $this->getArtistLabel($artist, $formatYear);
        $createdAt = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d') : null;


        return [
            'idAlbum' => $this->getIdAlbum(),
            'nom' => $this->getName(),
            'categ' => $this->getCateg(),
            'label' => $label,
            'cover' => $this->getCover(),
            'year' => $formatYear,
            'createdAt' => $createdAt,
            'songs' => $songs,
        ];
    }


    private function serializeSongs()
    {
        $songs = [];
        foreach ($this->getSongIdSong() as $song) {
            //$songs[] = $song->songSeriaizer();
        }
        return $songs;
    }

    private function formatYear()
    {
        $year = $this->getYear();
        return $year ? $year->format('Y') : null;
    }

    public function getArtistLabel($artist, $year)
    {
        $label = null;
        $labelHasArtist = $artist->getLabelHasArtist()->filter(function ($labelHasArtist) use ($year) {
            $joinedAt = $labelHasArtist->getSignAt();
            $leftAt = $labelHasArtist->getLeftAt();

            return $joinedAt <= $year && ($leftAt === null || $leftAt > $year);
        })->first();

        if ($labelHasArtist) {
            $label = $labelHasArtist->getIdLabel()->getLabelName();
        }
        return $label;
    }
}

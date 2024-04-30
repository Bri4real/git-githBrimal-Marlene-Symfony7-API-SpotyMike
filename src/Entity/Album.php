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

    #[ORM\Column(length: 90)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $categ = null;

    #[ORM\Column(length: 125)]
    private ?string $cover = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
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

    public function setIdAlbum(?string $idAlbum): string
    {
        if ($idAlbum !== null) {
            $this->idAlbum = $idAlbum;
        } else {
            $uuid = Uuid::v4();
            $this->idAlbum = 'spotify:album:' . $uuid;
        }
        return $this->idAlbum;
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
    public function getNom(): ?string
    {
        return $this->name;
    }

    public function setNom(string $name): static
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
            // set the owning side to null (unless already changed)
            if ($songIdSong->getAlbum() === $this) {
                $songIdSong->setAlbum(null);
            }
        }

        return $this;
    }

    public function albumSerializer()
    {
        $songs = $this->serializeSongs();
        $artist = $this->getArtistUserIdUser();
        $formatYear = $this->formatYear();
        $label = $this->getArtistLabel($artist, $formatYear);
        $createdAt = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d') : null;


        return [
            'idAlbum' => $this->getIdAlbum(),
            'nom' => $this->getNom(),
            'categ' => $this->getCateg(),
            'label' => $label,
            'cover' => $this->getCover(),
            'year' => $formatYear,
            'createdAt' => $createdAt,
            'songs' => $songs,
        ];
    }

    // Méthode privée pour sérialiser les chansons associées à l'album
    private function serializeSongs()
    {
        $songs = [];
        foreach ($this->getSongIdSong() as $song) {
            //    $songs[] = $song->songSeriaizer();
        }
        return $songs;
    }

    // Méthode privée pour formater l'année de l'album
    private function formatYear()
    {
        $year = $this->getYear();
        return $year ? $year->format('Y') : null;
    }

    // Méthode privée pour récupérer le label de l'artiste associé à l'album
    private function getArtistLabel($artist, $year)
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

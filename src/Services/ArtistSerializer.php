<?php

namespace App\Service;

use App\Entity\Artist;
use App\DTO\ArtistDTO;

class ArtistSerializerService
{
    public function artistAllSerializer(Artist $artist): ArtistDTO
    {
        $dto = new ArtistDTO();
        $dto->firstname = $artist->getUserIdUser()->getFirstname();
        $dto->lastname = $artist->getUserIdUser()->getLastname();
        $dto->fullname = $artist->getFullname();
        $dto->avatar = $artist->getAvatar();
        $dto->followers = $artist->getFollowers();
        $dto->sexe = $artist->getUserIdUser()->getSexe() === '1' ? 'Homme' : 'Femme';
        $dto->dateBirth = $artist->getUserIdUser()->getDateBirth() ? $artist->getUserIdUser()->getDateBirth()->format('d-m-Y') : null;
        $dto->createdAt = $artist->getCreatedAt() ? $artist->getCreatedAt()->format('Y-m-d') : null;
        $dto->albums = $artist->getAlbums()->map(function ($album) {
            return $album->albumSerializer();
        });

        return $dto;
    }

    public function artistSearchSerializer(Artist $artist): ArtistDTO
    {
        $dto = $this->artistAllSerializer($artist);
        $dto->featurings = $this->serializeFeaturings($artist);

        return $dto;
    }

    private function serializeFeaturings(Artist $artist): array
    {
        $featurings = [];
        foreach ($artist->getFeaturings() as $featuring) {
            $featurings[] = $featuring->featuringSerializer();
        }

        return $featurings;
    }
}

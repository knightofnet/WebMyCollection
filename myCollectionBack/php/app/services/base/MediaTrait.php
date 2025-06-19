<?php

namespace MyCollection\app\services\base;

use MyCollection\app\dto\entities\Media;
use MyCollection\app\utils\BddUtils;

trait MediaTrait
{
    /**
     * @return Media[]
     */
    public function getAllMedia(): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . Media::TABLE . " ORDER BY Id_Media",
            [], Media::class
        );
    }

    public function getMediaById(int $idMedia): ?Media
    {
        /** @var Media|null $obj */
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . Media::TABLE . " WHERE Id_Media = :idMedia",
            ['idMedia' => $idMedia], Media::class
        );
    }

    /**
     * @param int $idObjet
     * @return Media[]
     * @throws \Exception
     */
    public function getMediaByIdObjet(int $idObjet): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . Media::TABLE . " WHERE Id_Objet = :idObjet ORDER BY Id_Media",
            ['idObjet' => $idObjet], Media::class
        );
    }

    public function deleteMedia(int $idMedia): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . Media::TABLE . " WHERE Id_Media = :idMedia",
            ['idMedia' => $idMedia]
        );
    }

    public function deleteMediaByIdObjet(int $idObjet): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . Media::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet]
        );
    }

    public function addMedia(Media $media): bool
    {
        return BddUtils::executeOrderInsert(
            "INSERT INTO " . Media::TABLE . " (Type, UriServeur, EstPrincipal, Id_Objet) VALUES (:type, :uriServeur, :estPrincipal, :idObjet)",
            [
                'type' => $media->getType(),
                'uriServeur' => $media->getUriServeur(),
                'estPrincipal' => $media->isEstPrincipal(),
                'idObjet' => $media->getIdObjet(),
            ], $media
        );
    }

    public function updateMedia(Media $media): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "UPDATE " . Media::TABLE . " SET Type = :type, UriServeur = :uriServeur, EstPrincipal = :estPrincipal, Id_Objet = :idObjet WHERE Id_Media = :idMedia",
            [
                'type' => $media->getType(),
                'uriServeur' => $media->getUriServeur(),
                'estPrincipal' => $media->isEstPrincipal(),
                'idObjet' => $media->getIdObjet(),
                'idMedia' => $media->getIdMedia(),
            ]
        );
    }
}
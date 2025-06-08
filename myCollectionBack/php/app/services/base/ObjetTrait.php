<?php

namespace MyCollection\app\services\base;

use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\entities\Objet;
use MyCollection\app\utils\BddUtils;

trait ObjetTrait
{
    /**
     * @return Objet[]
     */
    public function getAllObjets(): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . Objet::TABLE . " ORDER BY Id_Objet",
            [], Objet::class
        );
    }

    public function getObjetById(int $idObjet): ?Objet
    {
        /** @var Objet|null $obj */
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . Objet::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet], Objet::class
        );
    }

    public function deleteObjet(int $idObjet): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . Objet::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet]
        );
    }

    public function addObjet(Objet $objet): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "INSERT INTO " . Objet::TABLE . " (Nom, Description, DateAcquisition, UrlAchat) VALUES (:nom, :description, :dateAcquisition, :urlAchat)",
            [
                'nom' => $objet->getNom(),
                'description' => $objet->getDescription(),
                'dateAcquisition' =>  $objet->getDateAcquisition() ? $objet->getDateAcquisition()->format(FormatCst::DateToBddFormat) : null,
                'urlAchat' => $objet->getUrlAchat(),
            ]
        );
    }

    public function updateObjet(Objet $objet): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "UPDATE " . Objet::TABLE . " SET Nom = :nom, Description = :description, DateAcquisition = :dateAcquisition, UrlAchat = :urlAchat WHERE Id_Objet = :idObjet",
            [
                'nom' => $objet->getNom(),
                'description' => $objet->getDescription(),
                'dateAcquisition' => $objet->getDateAcquisition() ? $objet->getDateAcquisition()->format(FormatCst::DateToBddFormat) : null,
                'urlAchat' => $objet->getUrlAchat(),
                'idObjet' => $objet->getIdObjet(),
            ]
        );
    }
}
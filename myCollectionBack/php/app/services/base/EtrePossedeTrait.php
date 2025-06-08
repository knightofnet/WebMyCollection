<?php

namespace MyCollection\app\services\base;

use MyCollection\app\dto\entities\EtrePossede;
use MyCollection\app\utils\BddUtils;

trait EtrePossedeTrait
{
    /**
     * @return EtrePossede[]
     */
    public function getAllEtrePossede(): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . EtrePossede::TABLE . " ORDER BY Id_Objet, Id_Proprietaire",
            [], EtrePossede::class

        );
    }

    public function getEtrePossedeById(int $idObjet, int $idProprietaire): ?EtrePossede
    {
        /** @var EtrePossede|null $obj */
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . EtrePossede::TABLE . " WHERE Id_Objet = :idObjet AND Id_Proprietaire = :idProprietaire",
            [
                'idObjet' => $idObjet,
                'idProprietaire' => $idProprietaire,
            ], EtrePossede::class
        );
    }

    public function getEtrePossedeByIdObjet(int $idObjet): ?EtrePossede
    {
        /** @var EtrePossede|null $obj */
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . EtrePossede::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet], EtrePossede::class

        );
    }

    public function deleteEtrePossede(int $idObjet, int $idProprietaire): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . EtrePossede::TABLE . " WHERE Id_Objet = :idObjet AND Id_Proprietaire = :idProprietaire",
            [
                'idObjet' => $idObjet,
                'idProprietaire' => $idProprietaire,
            ]
        );
    }

    public function deleteEtrePossedeByIdObjet(int $idObjet): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . EtrePossede::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet]
        );
    }

    public function addEtrePossede(EtrePossede $etrePossede): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(

            "INSERT INTO " . EtrePossede::TABLE . " (Id_Objet, Id_Proprietaire) VALUES (:idObjet, :idProprietaire)",
            [
                'idObjet' => $etrePossede->getIdObjet(),
                'idProprietaire' => $etrePossede->getIdProprietaire(),
            ]
        );
    }

    public function updateEtrePossede(EtrePossede $etrePossede, int $oldIdObjet, int $oldIdProprietaire): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(

            "UPDATE " . EtrePossede::TABLE . " SET Id_Objet = :idObjet, Id_Proprietaire = :idProprietaire WHERE Id_Objet = :oldIdObjet AND Id_Proprietaire = :oldIdProprietaire",
            [
                'idObjet' => $etrePossede->getIdObjet(),
                'idProprietaire' => $etrePossede->getIdProprietaire(),
                'oldIdObjet' => $oldIdObjet,
                'oldIdProprietaire' => $oldIdProprietaire,
            ]
        );
    }
}
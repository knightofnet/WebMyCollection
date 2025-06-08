<?php

namespace MyCollection\app\services\base;

use MyCollection\app\dto\entities\TyCategorie;
use MyCollection\app\utils\BddUtils;

trait TyCategorieTrait
{
    /**
     * @return TyCategorie[]
     */
    public function getAllTyCategories(): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . TyCategorie::TABLE . " ORDER BY Id_TyCategorie",
            [], TyCategorie::class
        );
    }

    public function getTyCategorieById(int $idTyCategorie): ?TyCategorie
    {
        /** @var TyCategorie|null $obj */
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . TyCategorie::TABLE . " WHERE Id_TyCategorie = :idTyCategorie",
            ['idTyCategorie' => $idTyCategorie], TyCategorie::class
        );
    }

    public function deleteTyCategorie(int $idTyCategorie): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . TyCategorie::TABLE . " WHERE Id_TyCategorie = :idTyCategorie",
            ['idTyCategorie' => $idTyCategorie]
        );
    }

    public function addTyCategorie(TyCategorie $tyCategorie): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "INSERT INTO " . TyCategorie::TABLE . " (NomCat) VALUES (:nomCat)",
            [
                'nomCat' => $tyCategorie->getNomCat(),
            ]
        );
    }

    public function updateTyCategorie(TyCategorie $tyCategorie): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "UPDATE " . TyCategorie::TABLE . " SET NomCat = :nomCat WHERE Id_TyCategorie = :idTyCategorie",
            [
                'nomCat' => $tyCategorie->getNomCat(),
                'idTyCategorie' => $tyCategorie->getIdTyCategorie(),
            ]
        );
    }
}
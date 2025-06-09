<?php

namespace MyCollection\app\services\base;

use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\utils\BddUtils;

trait CategorieTrait
{
    /**
     * @return Categorie[]
     */
    public function getAllCategories(): array
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . Categorie::TABLE . " GROUP BY Id_Categorie ORDER BY Nom",
            [],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception) {
                    return [];
                }
                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $categorie = new Categorie();
                    $categorie->hydrateObjFromRow($row);
                    $retArray[] = $categorie;
                }
                return $retArray;
            }
        );
    }

    public function getCategorieById(int $idCategorie): ?Categorie
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . Categorie::TABLE . " WHERE Id_Categorie = :idCategorie",
            ['idCategorie' => $idCategorie],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return null;
                }
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $categorie = new Categorie();
                $categorie->hydrateObjFromRow($row);
                return $categorie;
            }
        );
    }

    public function deleteCategorieById(int $idCategorie): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "DELETE FROM " . Categorie::TABLE . " WHERE Id_Categorie = :idCategorie",
            ['idCategorie' => $idCategorie],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function addCategorie(Categorie $categorie): bool
    {
        return BddUtils::executeOrderInsert(
            "INSERT INTO " . Categorie::TABLE . " (NomUnique, Nom, StyleMainColor, StyleSecondaryColor, Id_TyCategorie) VALUES (:nomunique, :nom, :styleMainColor, :styleSecondaryColor, :idTyCategorie)",
            [
                'nomunique' => $categorie->getNomUnique(),
                'nom' => $categorie->getNom(),
                'styleMainColor' => $categorie->getStyleMainColor(),
                'styleSecondaryColor' => $categorie->getStyleSecondaryColor(),
                'idTyCategorie' => $categorie->getIdTyCategorie(),
            ], $categorie
        );
    }

    public function updateCategorie(Categorie $categorie): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "UPDATE " . Categorie::TABLE . " SET Nom = :nom, StyleMainColor = :styleMainColor, StyleSecondaryColor = :styleSecondaryColor, Id_TyCategorie = :idTyCategorie WHERE Id_Categorie = :idCategorie",
            [
                'idCategorie' => $categorie->getIdCategorie(),
                'nom' => $categorie->getNom(),
                'styleMainColor' => $categorie->getStyleMainColor(),
                'styleSecondaryColor' => $categorie->getStyleSecondaryColor(),
                'idTyCategorie' => $categorie->getIdTyCategorie(),
            ],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }
}
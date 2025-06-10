<?php

namespace MyCollection\app\services\base;

use MyCollection\app\dto\entities\AvoirCategorie;
use MyCollection\app\utils\BddUtils;

trait AvoirCategorieTrait
{

    /*
     *     public function getAllAccessTokens(): array
  public function getAccessTokenById(int $id): ?AccesToken
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . AccesToken::TABLE . " WHERE Id_AccesToken = :id",
            ['id' => $id],
            function (? \PDOStatement $stmt, ? \Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return null;
                }

                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $accesToken = new AccesToken();
                $accesToken->hydrateObjFromRow($row);
                return $accesToken;
            }
        );
    }

    public function deleteAccessTokenById(int $id): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "DELETE FROM " . AccesToken::TABLE . " WHERE Id_AccesToken = :id",
            ['id' => $id],
            function (? \PDOStatement $stmt, ? \Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function addAccessToken(AccesToken $accesToken): bool {
        return BddUtils::executeOrder(
            self::getConnection(),
            "INSERT INTO " . AccesToken::TABLE . " (UniqueId, DeviceId, DateCreation) VALUES (:uniqueId, :deviceId, :dateCreation)",
            [
                'uniqueId' => $accesToken->getUniqueId(),
                'deviceId' => $accesToken->getDeviceId(),
                'dateCreation' => $accesToken->getDateCreation()->format(FormatCst::DateToBddFormat),
            ],
            function (? \PDOStatement $stmt, ? \Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );

    }

    public function updateAccessToken(AccesToken $accesToken): bool {
        return BddUtils::executeOrder(
            self::getConnection(),
            "UPDATE " . AccesToken::TABLE . " SET UniqueId = :uniqueId, DeviceId = :deviceId, DateCreation = :dateCreation WHERE Id_AccesToken = :id",
            [
                'id' => $accesToken->getIdAccesToken(),
                'uniqueId' => $accesToken->getUniqueId(),
                'deviceId' => $accesToken->getDeviceId(),
                'dateCreation' => $accesToken->getDateCreation()->format(FormatCst::DateToBddFormat),
            ],
            function (? \PDOStatement $stmt, ? \Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }
}
     */


    /**
     * @return AvoirCategorie[]
     */
    public function getAllAvoirCategorie(): array
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . AvoirCategorie::TABLE . " ORDER BY Id_Objet, Id_Categorie",
            [],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception) {
                    return [];
                }

                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $avoirCategorie = new AvoirCategorie();
                    $avoirCategorie->hydrateObjFromRow($row);
                    $retArray[] = $avoirCategorie;
                }

                return $retArray;
            }
        );

    }

    public function getAvoirCategorieByIdObjet(int $idObjet): ?AvoirCategorie
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . AvoirCategorie::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return null;
                }

                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $avoirCategorie = new AvoirCategorie();
                $avoirCategorie->hydrateObjFromRow($row);
                return $avoirCategorie;
            }
        );
    }

    public function getAvoirCategorieByIdCategorie(int $idCategorie): ?AvoirCategorie
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . AvoirCategorie::TABLE . " WHERE Id_Categorie = :idCategorie",
            ['idCategorie' => $idCategorie],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return null;
                }

                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $avoirCategorie = new AvoirCategorie();
                $avoirCategorie->hydrateObjFromRow($row);
                return $avoirCategorie;
            }
        );
    }

    public function deleteAvoirCategorieByIdObjet(int $idObjet = null, int $idCategorie = null): bool
    {
        if ($idObjet === null && $idCategorie === null) {
            throw new \InvalidArgumentException("At least one of idObjet or idCategorie must be provided.");
        }



        $sql = "DELETE FROM " . AvoirCategorie::TABLE . " WHERE ";
        $params = [];
        if ($idObjet !== null) {
            $sql .= "Id_Objet = :idObjet";
            $params['idObjet'] = $idObjet;
        }

        if ($idCategorie !== null) {
            if (!empty($params)) {
                $sql .= " AND ";
            }
            $sql .= "Id_Categorie = :idCategorie";
            $params['idCategorie'] = $idCategorie;
        }

        return BddUtils::executeOrder(
            self::getConnection(),
            $sql,
            $params,
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function addAvoirCategorie(AvoirCategorie $avoirCategorie): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "INSERT INTO " . AvoirCategorie::TABLE . " (Id_Objet, Id_Categorie) VALUES (:idObjet, :idCategorie)",
            [
                'idObjet' => $avoirCategorie->getIdObjet(),
                'idCategorie' => $avoirCategorie->getIdCategorie(),
            ],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }


}
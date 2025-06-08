<?php

namespace MyCollection\app\services\base;

use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\entities\AccesToken;
use MyCollection\app\utils\BddUtils;

trait AccesTokenTrait
{



    /**
     * @return AccesToken[]
     */
    public function getAllAccessTokens(): array
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . AccesToken::TABLE . " ORDER BY DateCreation DESC",
            [],
            function (? \PDOStatement $stmt, ? \Exception $exception) {
                if ($exception) {
                    return [];
                }

                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $accesToken = new AccesToken();
                    $accesToken->hydrateObjFromRow($row);
                    $retArray[] = $accesToken;
                }

                return $retArray;
            }

        );
    }

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
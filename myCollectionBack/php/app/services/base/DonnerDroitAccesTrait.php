<?php

namespace MyCollection\app\services\base;

use Enigmas\app\dto\entities\DonnerDroitAcces;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\utils\BddUtils;

trait DonnerDroitAccesTrait
{
    /**
     * @return DonnerDroitAcces[]
     */
    public function getAllDonnerDroitAcces(): array
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . DonnerDroitAcces::TABLE . " ORDER BY Id_Objet, Id_Proprietaire, Id_AccesToken",
            [],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception) {
                    return [];
                }
                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $donnerDroitAcces = new DonnerDroitAcces();
                    $donnerDroitAcces->hydrateObjFromRow($row);
                    $retArray[] = $donnerDroitAcces;
                }
                return $retArray;
            }
        );
    }

    public function getDonnerDroitAccesById(int $idObjet, int $idProprietaire, int $idAccesToken): ?DonnerDroitAcces
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . DonnerDroitAcces::TABLE . " WHERE Id_Objet = :idObjet AND Id_Proprietaire = :idProprietaire AND Id_AccesToken = :idAccesToken",
            [
                'idObjet' => $idObjet,
                'idProprietaire' => $idProprietaire,
                'idAccesToken' => $idAccesToken,
            ],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return null;
                }
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $donnerDroitAcces = new DonnerDroitAcces();
                $donnerDroitAcces->hydrateObjFromRow($row);
                return $donnerDroitAcces;
            }
        );
    }

    /**
     * @param int $idObjet
     * @return DonnerDroitAcces[]
     * @throws \Exception
     */
    public function getDonnerDroitAccesByIdObjet(int $idObjet): array
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . DonnerDroitAcces::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return [];
                }
                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $donnerDroitAcces = new DonnerDroitAcces();
                    $donnerDroitAcces->hydrateObjFromRow($row);
                    $retArray[] = $donnerDroitAcces;
                }
                return $retArray;
            }
        );
    }

    /**
     * @param int $idProprietaire
     * @return DonnerDroitAcces[]
     * @throws \Exception
     */
    public function getDonnerDroitAccesByIdProprietaire(int $idProprietaire): array {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT * FROM " . DonnerDroitAcces::TABLE . " WHERE Id_Proprietaire = :idProprietaire",
            ['idProprietaire' => $idProprietaire],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return [];
                }
                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $donnerDroitAcces = new DonnerDroitAcces();
                    $donnerDroitAcces->hydrateObjFromRow($row);
                    $retArray[] = $donnerDroitAcces;
                }
                return $retArray;
            }
        );
    }

    public function deleteDonnerDroitAcces(int $idObjet, int $idProprietaire, int $idAccesToken): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "DELETE FROM " . DonnerDroitAcces::TABLE . " WHERE Id_Objet = :idObjet AND Id_Proprietaire = :idProprietaire AND Id_AccesToken = :idAccesToken",
            [
                'idObjet' => $idObjet,
                'idProprietaire' => $idProprietaire,
                'idAccesToken' => $idAccesToken,
            ],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function deleteDonnerDroitAccesByIdObjet(int $idObjet): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "DELETE FROM " . DonnerDroitAcces::TABLE . " WHERE Id_Objet = :idObjet",
            ['idObjet' => $idObjet],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function deleteDonnerDroitAccesByIdProprietaire(int $idProprietaire): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "DELETE FROM " . DonnerDroitAcces::TABLE . " WHERE Id_Proprietaire = :idProprietaire",
            ['idProprietaire' => $idProprietaire],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function addDonnerDroitAcces(DonnerDroitAcces $donnerDroitAcces): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "INSERT INTO " . DonnerDroitAcces::TABLE . " (Id_Objet, Id_Proprietaire, Id_AccesToken, DateDeb, DateFin, NiveauDetail) VALUES (:idObjet, :idProprietaire, :idAccesToken, :dateDeb, :dateFin, :niveauDetail)",
            [
                'idObjet' => $donnerDroitAcces->getIdObjet(),
                'idProprietaire' => $donnerDroitAcces->getIdProprietaire(),
                'idAccesToken' => $donnerDroitAcces->getIdAccesToken(),
                'dateDeb' => $donnerDroitAcces->getDateDeb()->format(FormatCst::DateToBddFormat),
                'dateFin' => $donnerDroitAcces->getDateFin() ? $donnerDroitAcces->getDateFin()->format(FormatCst::DateToBddFormat) : null,
                'niveauDetail' => $donnerDroitAcces->getNiveauDetail(),
            ],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public function updateDonnerDroitAcces(DonnerDroitAcces $donnerDroitAcces): bool
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "UPDATE " . DonnerDroitAcces::TABLE . " SET DateDeb = :dateDeb, DateFin = :dateFin, NiveauDetail = :niveauDetail WHERE Id_Objet = :idObjet AND Id_Proprietaire = :idProprietaire AND Id_AccesToken = :idAccesToken",
            [
                'idObjet' => $donnerDroitAcces->getIdObjet(),
                'idProprietaire' => $donnerDroitAcces->getIdProprietaire(),
                'idAccesToken' => $donnerDroitAcces->getIdAccesToken(),
                'dateDeb' => $donnerDroitAcces->getDateDeb()->format(FormatCst::DateToBddFormat),
                'dateFin' => $donnerDroitAcces->getDateFin() ? $donnerDroitAcces->getDateFin()->format(FormatCst::DateToBddFormat) : null,
                'niveauDetail' => $donnerDroitAcces->getNiveauDetail(),
            ],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return !$exception && $stmt && $stmt->rowCount() > 0;
            }
        );
    }
}
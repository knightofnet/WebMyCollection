<?php

namespace MyCollection\app\services\base;

use MyCollection\app\dto\entities\Proprietaire;
use MyCollection\app\utils\BddUtils;

trait ProprietaireTrait
{
    /**
     * @return Proprietaire[]
     */
    public function getAllProprietaires(): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . Proprietaire::TABLE . " ORDER BY Id_Proprietaire",
            [], Proprietaire::class
        );
    }

    public function getProprietaireById(int $idProprietaire): ?Proprietaire
    {
        /** @var Proprietaire|null $obj */
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . Proprietaire::TABLE . " WHERE Id_Proprietaire = :idProprietaire",
            ['idProprietaire' => $idProprietaire], Proprietaire::class
        );
    }

    public function deleteProprietaire(int $idProprietaire): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "DELETE FROM " . Proprietaire::TABLE . " WHERE Id_Proprietaire = :idProprietaire",
            ['idProprietaire' => $idProprietaire]
        );
    }

    public function addProprietaire(Proprietaire $proprietaire): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "INSERT INTO " . Proprietaire::TABLE . " (Nom, HashCodePin, Email) VALUES (:nom, :hashCodePin, :email)",
            [
                'nom' => $proprietaire->getNom(),
                'hashCodePin' => $proprietaire->getHashCodePin(),
                'email' => $proprietaire->getEmail(),
            ]
        );
    }

    public function updateProprietaire(Proprietaire $proprietaire): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "UPDATE " . Proprietaire::TABLE . " SET Nom = :nom, HashCodePin = :hashCodePin, Email = :email WHERE Id_Proprietaire = :idProprietaire",
            [
                'nom' => $proprietaire->getNom(),
                'hashCodePin' => $proprietaire->getHashCodePin(),
                'email' => $proprietaire->getEmail(),
                'idProprietaire' => $proprietaire->getIdProprietaire(),
            ]
        );
    }
}
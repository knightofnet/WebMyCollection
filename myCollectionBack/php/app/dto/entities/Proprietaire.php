<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\dto\IToArray;

class Proprietaire implements IToArray
{
    public const TABLE = 'Proprietaire';
    private ?int $Id_Proprietaire = null;
    private string $Nom = '';
    private string $HashCodePin = '';
    private ?string $Email = null;

    public function getIdProprietaire(): ?int
    {
        return $this->Id_Proprietaire;
    }

    public function setIdProprietaire(?int $Id_Proprietaire): Proprietaire
    {
        $this->Id_Proprietaire = $Id_Proprietaire;
        return $this;
    }

    public function getNom(): string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): Proprietaire
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getHashCodePin(): string
    {
        return $this->HashCodePin;
    }

    public function setHashCodePin(string $HashCodePin): Proprietaire
    {
        $this->HashCodePin = $HashCodePin;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(?string $Email): Proprietaire
    {
        $this->Email = $Email;
        return $this;
    }




    public function toArray(): array
    {
        return [
            'Id_Proprietaire' => $this->Id_Proprietaire,
            'Nom'             => $this->Nom,
            'HashCodePin'     => $this->HashCodePin,
            'Email'           => $this->Email,
        ];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Proprietaire = isset($row['Id_Proprietaire']) ? (int)$row['Id_Proprietaire'] : null;
        $this->Nom             = $row['Nom'] ?? $this->Nom;
        $this->HashCodePin     = $row['HashCodePin'] ?? $this->HashCodePin;
        $this->Email           = $row['Email'] ?? null;
    }

    function setIdObj(int $id): IToArray
    {
        return $this->setIdProprietaire($id);
    }


}
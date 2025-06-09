<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\dto\IToArray;

class Categorie implements IToArray
{
    public const TABLE = 'Categorie';
    private ?int $Id_Categorie = null;

    private string $NomUnique = '';
    private string $Nom = '';
    private ?string $StyleMainColor = null;
    private ?string $StyleSecondaryColor = null;
    private ?int $Id_TyCategorie = null;

    public function getIdCategorie(): ?int
    {
        return $this->Id_Categorie;
    }

    public function setIdCategorie(?int $Id_Categorie): Categorie
    {
        $this->Id_Categorie = $Id_Categorie;
        return $this;
    }

    public function getNom(): string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): Categorie
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getStyleMainColor(): ?string
    {
        return $this->StyleMainColor;
    }

    public function setStyleMainColor(?string $StyleMainColor): Categorie
    {
        $this->StyleMainColor = $StyleMainColor;
        return $this;
    }

    public function getStyleSecondaryColor(): ?string
    {
        return $this->StyleSecondaryColor;
    }

    public function setStyleSecondaryColor(?string $StyleSecondaryColor): Categorie
    {
        $this->StyleSecondaryColor = $StyleSecondaryColor;
        return $this;
    }

    public function getIdTyCategorie(): ?int
    {
        return $this->Id_TyCategorie;
    }

    public function setIdTyCategorie(?int $Id_TyCategorie): Categorie
    {
        $this->Id_TyCategorie = $Id_TyCategorie;
        return $this;
    }

    public function getNomUnique(): string
    {
        return $this->NomUnique;
    }

    public function setNomUnique(string $NomUnique): Categorie
    {
        $this->NomUnique = $NomUnique;
        return $this;
    }




    public function toArray(): array
    {
        return [
            'Id_Categorie'        => $this->Id_Categorie,
            'NomUnique'           => $this->NomUnique,
            'Nom'                 => $this->Nom,
            'StyleMainColor'      => $this->StyleMainColor,
            'StyleSecondaryColor' => $this->StyleSecondaryColor,
            'Id_TyCategorie'      => $this->Id_TyCategorie,
        ];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Categorie        = isset($row['Id_Categorie']) ? (int)$row['Id_Categorie'] : null;
        $this->NomUnique           = $row['NomUnique'] ?? $this->NomUnique;
        $this->Nom                 = $row['Nom'] ?? $this->Nom;
        $this->StyleMainColor      = $row['StyleMainColor'] ?? null;
        $this->StyleSecondaryColor = $row['StyleSecondaryColor'] ?? null;
        $this->Id_TyCategorie      = isset($row['Id_TyCategorie']) ? (int)$row['Id_TyCategorie'] : null;
    }

    function setIdObj(int $id): IToArray
    {
        return $this->setIdCategorie($id);
    }
}
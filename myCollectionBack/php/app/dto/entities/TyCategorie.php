<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\dto\IToArray;

class TyCategorie implements IToArray
{
    public const TABLE = 'TyCategorie';
    private ?int $Id_TyCategorie = null;
    private string $NomCat = '';

    public function getIdTyCategorie(): ?int
    {
        return $this->Id_TyCategorie;
    }

    public function setIdTyCategorie(?int $Id_TyCategorie): TyCategorie
    {
        $this->Id_TyCategorie = $Id_TyCategorie;
        return $this;
    }

    public function getNomCat(): string
    {
        return $this->NomCat;
    }

    public function setNomCat(string $NomCat): TyCategorie
    {
        $this->NomCat = $NomCat;
        return $this;
    }



    public function toArray(): array
    {
        return ['Id_TyCategorie' => $this->Id_TyCategorie, 'NomCat' => $this->NomCat];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_TyCategorie = isset($row['Id_TyCategorie']) ? (int)$row['Id_TyCategorie'] : null;
        $this->NomCat         = $row['NomCat'] ?? $this->NomCat;
    }

    function setIdObj(int $id): IToArray
    {
        return $this->setIdTyCategorie($id);
    }


}
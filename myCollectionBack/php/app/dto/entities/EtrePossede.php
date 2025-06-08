<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\dto\IToArray;

class EtrePossede implements IToArray
{
    public const TABLE = 'EtrePossede';
    private int $Id_Objet = 0;
    private int $Id_Proprietaire = 0;

    public function getIdObjet(): int
    {
        return $this->Id_Objet;
    }

    public function setIdObjet(int $Id_Objet): EtrePossede
    {
        $this->Id_Objet = $Id_Objet;
        return $this;
    }

    public function getIdProprietaire(): int
    {
        return $this->Id_Proprietaire;
    }

    public function setIdProprietaire(int $Id_Proprietaire): EtrePossede
    {
        $this->Id_Proprietaire = $Id_Proprietaire;
        return $this;
    }



    public function toArray(): array
    {
        return ['Id_Objet' => $this->Id_Objet, 'Id_Proprietaire' => $this->Id_Proprietaire];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Objet        = isset($row['Id_Objet']) ? (int)$row['Id_Objet'] : $this->Id_Objet;
        $this->Id_Proprietaire = isset($row['Id_Proprietaire']) ? (int)$row['Id_Proprietaire'] : $this->Id_Proprietaire;
    }
}
<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\dto\IToArray;

class AvoirCategorie implements IToArray
{
    public const TABLE = 'AvoirCategorie';
    private int $Id_Objet = 0;
    private int $Id_Categorie = 0;

    public function __construct(int $Id_Objet = 0, int $Id_Categorie = 0)
    {
        $this->Id_Objet = $Id_Objet;
        $this->Id_Categorie = $Id_Categorie;
    }

    public function getIdObjet(): int
    {
        return $this->Id_Objet;
    }

    public function setIdObjet(int $Id_Objet): AvoirCategorie
    {
        $this->Id_Objet = $Id_Objet;
        return $this;
    }

    public function getIdCategorie(): int
    {
        return $this->Id_Categorie;
    }

    public function setIdCategorie(int $Id_Categorie): AvoirCategorie
    {
        $this->Id_Categorie = $Id_Categorie;
        return $this;
    }



    public function toArray(): array
    {
        return ['Id_Objet' => $this->Id_Objet, 'Id_Categorie' => $this->Id_Categorie];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Objet     = isset($row['Id_Objet']) ? (int)$row['Id_Objet'] : $this->Id_Objet;
        $this->Id_Categorie = isset($row['Id_Categorie']) ? (int)$row['Id_Categorie'] : $this->Id_Categorie;
    }

    function setIdObj(int $id): IToArray
    {
        return $this;
    }
}
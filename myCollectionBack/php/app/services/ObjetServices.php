<?php

namespace MyCollection\app\services;

use MyCollection\app\dto\entities\AvoirCategorie;
use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\dto\entities\EtrePossede;
use MyCollection\app\dto\entities\Objet;
use MyCollection\app\services\base\AvoirCategorieTrait;
use MyCollection\app\services\base\CategorieTrait;
use MyCollection\app\services\base\EtrePossedeTrait;
use MyCollection\app\services\base\ObjetTrait;
use MyCollection\app\services\base\TyCategorieTrait;
use MyCollection\app\utils\BddUtils;

class ObjetServices extends AbstractServices
{


    use ObjetTrait;
    use EtrePossedeTrait;



    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Objet[]
     */
    public function getObjetsByIdProprietaire(int $idProprietaire): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT o.* FROM " . Objet::TABLE . " o
            JOIN " . EtrePossede::TABLE . " ep ON o.Id_Objet = ep.Id_Objet
            WHERE ep.Id_Proprietaire = :idProprietaire
            ORDER BY o.Id_Objet",
            ['idProprietaire' => $idProprietaire],
            Objet::class
        );
    }

    public function getCategoriesByIdObjet(int $idObjet): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT c.* FROM " . Categorie::TABLE . " c
            JOIN " . AvoirCategorie::TABLE . " ac ON c.Id_Categorie = ac.Id_Categorie
            WHERE ac.Id_Objet = :idObjet
            ORDER BY c.Id_Categorie",
            ['idObjet' => $idObjet],
            Categorie::class
        );
    }




}
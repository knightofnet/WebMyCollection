<?php

namespace MyCollection\app\services;

use MyCollection\app\dto\entities\EtrePossede;
use MyCollection\app\dto\entities\Objet;
use MyCollection\app\services\base\ObjetTrait;
use MyCollection\app\utils\BddUtils;

class ObjetServices extends AbstractServices
{


    use ObjetTrait;

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



}
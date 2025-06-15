<?php

namespace MyCollection\app\services;

use MyCollection\app\dto\entities\EtrePossede;
use MyCollection\app\dto\entities\Proprietaire;
use MyCollection\app\services\base\LoginTokenTrait;
use MyCollection\app\services\base\ProprietaireTrait;
use MyCollection\app\utils\BddUtils;

class ProprietaireService extends AbstractServices
{

    use ProprietaireTrait;
    use LoginTokenTrait;

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param int $idObjet
     * @return Proprietaire[]
     * @throws \Exception
     */
    public function getProprietairesByIdObjet(int $idObjet): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT *  " .
            "FROM " . Proprietaire::TABLE . " p " .
            "INNER JOIN " . EtrePossede::TABLE . " e ON p.Id_Proprietaire = e.Id_Proprietaire " .
            "WHERE e.Id_Objet = :idObjet ",
            ['idObjet' => $idObjet],
            Proprietaire::class

        );
    }

    public function getProprietaireByName(string $propName) : ?Proprietaire
    {
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . Proprietaire::TABLE . " WHERE Nom = :nom",
            ['nom' => $propName],
            Proprietaire::class
        );

    }



}
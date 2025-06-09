<?php

namespace MyCollection\app\services;

use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\services\AbstractServices;
use MyCollection\app\services\base\AvoirCategorieTrait;
use MyCollection\app\services\base\CategorieTrait;
use MyCollection\app\utils\BddUtils;

class CategorieServices extends AbstractServices
{
    use CategorieTrait;
    use AvoirCategorieTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCategorieByNomUniqueAndType(string $nomUnique, int $idTyCat = -1) : ?Categorie
    {
        $sql = "SELECT * FROM " . Categorie::TABLE . " WHERE NomUnique = :nomUnique";
        $params = ['nomUnique' => $nomUnique];
        if ($idTyCat > 0) {
            $sql .= " AND Id_TyCategorie = :idTyCat";
            $params['idTyCat'] = $idTyCat;
        }

        return BddUtils::executeOrderAndGetOne(
            $sql,
            $params,
            Categorie::class
        );
    }



}
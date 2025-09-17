<?php

namespace MyCollection\app\services;

use MyCollection\app\dto\entities\Categorie;
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

    public function getCategorieByNomUniqueAndType(string $nomUnique, int $idTyCat = -1): ?Categorie
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

    /**
     * @return Categorie[]
     */
    public function getLastCategories(int $nbLast)
    {
        $sql = "
select C.*, count(*) as ct 
from categorie C 
    inner join avoircategorie A on C.Id_Categorie = A.Id_Categorie 
    inner join objet O on A.Id_Objet = O.Id_Objet 
where c.Id_TyCategorie != 2 
group by C.Id_Categorie 
having ct > 1 order by ct desc limit :nbLast";
        return BddUtils::executeOrderAndGetMany(
            $sql,
            ['nbLast' => [$nbLast, \PDO::PARAM_INT]],
            Categorie::class
        );
    }


}
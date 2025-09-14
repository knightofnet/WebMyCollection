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

    /**
     * @param string $namePart
     * @return string[]
     */
    public function getObjetNamesLike(string $namePart) : array
    {
        return BddUtils::executeOrder(
            self::getConnection(),
            "SELECT DISTINCT Nom FROM " . Objet::TABLE . " WHERE Nom LIKE :namePart ORDER BY DateAjout",
            ['namePart' => '%' . $namePart . '%'],
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                if ($exception) {
                    return [];
                }

                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $objet = $row['Nom'] ?? null;
                    if ($objet !== null) {
                        $retArray[] = $objet;
                    }
                }

                return $retArray;
            }
        );



    }

    /**
     * @param int $nbLast
     * @return Objet[]
     */
    public function getLastAddedObject(int $nbLast) : array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . Objet::TABLE . " ORDER BY DateAjout DESC LIMIT :nbLast",
            ['nbLast' => [$nbLast, \PDO::PARAM_INT]],
            Objet::class
        );
    }


}
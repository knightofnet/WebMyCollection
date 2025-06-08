<?php

namespace MyCollection\app\controllers;

use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;
use MyCollection\app\dto\entities\Media;
use MyCollection\app\dto\entities\Proprietaire;
use MyCollection\app\services\MediaServices;
use MyCollection\app\services\ObjetServices;
use MyCollection\app\services\ProprietaireService;
use MyCollection\app\services\Services;
use MyCollection\app\utils\AppUtils;

class ObjetController extends AbstractController
{
    private ObjetServices $objetServices;
    private ProprietaireService  $proprietaireService;

    private MediaServices $mediaServices;

    public function __construct()
    {
        $this->objetServices = Services::instance()->getObjetServices();
        $this->proprietaireService = Services::instance()->getProprietaireService();
        $this->mediaServices = Services::instance()->getMediaServices();
    }

    /** @noinspection PhpUnused */
    public function getAllByUserId(int $userId): ResponseObject {

        $objets = $this->objetServices->getObjetsByIdProprietaire($userId);

        $objetsArray = AppUtils::toArrayIToArray($objets);
        $toFilterKey = [];

        foreach ($objetsArray as &$objet) {
            $idObjet = $objet['Id_Objet'];


            $objet[Proprietaire::TABLE] = $this->proprietaireService->getProprietairesByIdObjet($idObjet);
            $toFilterKey = array_merge($toFilterKey, ['HashCodePin', 'Email']);

            $objet[Media::TABLE] = $this->mediaServices->getMediaByIdObjet($idObjet);
        }

        $retArray = ResponseUtils::getDefaultResponseArray(true);
        $retArray['content']['data'] = $objetsArray;
        $retArray['content']['type'] = 'Objet[]';

        return ResponseObject::ResultsObjectToJson(AppUtils::toArrayIToArray($retArray, $toFilterKey) );


    }


}
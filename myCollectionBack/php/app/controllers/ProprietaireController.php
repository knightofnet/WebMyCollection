<?php

namespace MyCollection\app\controllers;

use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;
use MyCollection\app\services\ProprietaireService;
use MyCollection\app\utils\AppUtils;

class ProprietaireController extends AbstractController
{

    private ProprietaireService  $proprietaireService;

    public function __construct()
    {
        $this->proprietaireService = new ProprietaireService();
    }

    public function getAllProprietaires() : ResponseObject
    {

        $proprietaires = $this->proprietaireService->getAllProprietaires();

        $retArray = ResponseUtils::getDefaultResponseArray(true);
        $retArray['content']['data'] = AppUtils::toArrayIToArray($proprietaires, ['HashCodePin', 'Email']);
        $retArray['content']['type'] = 'Proprietaire[]';




        return ResponseObject::ResultsObjectToJson($retArray);

    }

}
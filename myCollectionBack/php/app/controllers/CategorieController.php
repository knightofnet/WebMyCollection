<?php

namespace MyCollection\app\controllers;

use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;
use MyCollection\app\services\CategorieServices;
use MyCollection\app\utils\AppUtils;

class CategorieController extends AbstractController
{

    private CategorieServices $categorieServices;

    public function __construct()
    {
        $this->categorieServices = new CategorieServices();
    }

    public function getAllCategories() : ResponseObject {

        $categories = $this->categorieServices->getAllCategories();

        $retArray = ResponseUtils::getDefaultResponseArray(true);
        $retArray['content']['data'] = AppUtils::toArrayIToArray($categories);
        $retArray['content']['type'] = 'Categorie[]';

        return ResponseObject::ResultsObjectToJson($retArray);


    }


}
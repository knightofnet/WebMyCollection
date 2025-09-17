<?php

namespace MyCollection\app\controllers;

use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;
use MyCollection\app\dto\ResponsePropsObject;
use MyCollection\app\services\CategorieServices;
use MyCollection\app\utils\AppUtils;
use MyCollection\app\utils\lang\ArrayUtils;

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

    public function getLastCategories(int $nbLast) : ResponseObject
    {

        $respObj = new ResponsePropsObject();


        $nbLast = intval($nbLast);
        if ($nbLast <= 0) {
            $nbLast = 5;
        }

        try {

            $categories = $this->categorieServices->getLastCategories($nbLast);
            $catRet = ArrayUtils::map($categories, fn($cat) => $cat->toArray());


            $respObj->setResult(true)
                ->setData($catRet)
                ->setType('Category[]');


        } catch (\Exception $e) {

            $respObj->setResult(false)
                ->setErrorMsg($e->getMessage())
                ->setErrCode(500);


        }

        return ResponseObject::ResultsObjectToJson($respObj->toArray());

    }



}
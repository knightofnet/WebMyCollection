<?php

namespace MyCollection\app\controllers;

use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;

class UserController extends AbstractController
{

    public function testResponse() : ResponseObject {

        $retArray = ResponseUtils::getDefaultResponseArray(true);


        return ResponseObject::ResultsObjectToJson($retArray);
    }

}
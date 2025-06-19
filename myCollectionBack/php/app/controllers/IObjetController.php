<?php

namespace MyCollection\app\controllers;

use MiniPhpRest\core\ResponseObject;

interface IObjetController
{
    public function getAllByUserId(int $userId): ResponseObject;

    public function getObjetById(int $objetId): ResponseObject;

    public function addNewObjet(): ResponseObject;

    public function updateObjet(): ResponseObject;

    public function addMediaForObjet(): ResponseObject;
}
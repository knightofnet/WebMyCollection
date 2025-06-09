<?php

namespace MyCollection\app\services;

use MyCollection\app\services\AbstractServices;
use MyCollection\app\services\base\MediaTrait;

class MediaServices extends AbstractServices
{

    use MediaTrait;


    public function __construct()
    {
        parent::__construct();
    }

}
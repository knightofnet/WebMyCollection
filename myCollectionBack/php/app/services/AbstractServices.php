<?php

namespace MyCollection\app\services;

use MyCollection\app\utils\BddUtils;

class AbstractServices
{

    private static \PDO $connection;

    protected function __construct()
    {

        self::initConnexion();

    }

    public static function getConnection(): \PDO
    {
        self::initConnexion();

        return self::$connection;
    }

    /**
     * @return void
     */
    public static function initConnexion(): void
    {
        if (!isset(self::$connection)) {
            self::$connection = BddUtils::initConnection(SERVER_ROOT . SITE_SECRET_FILE);
        }
    }

}
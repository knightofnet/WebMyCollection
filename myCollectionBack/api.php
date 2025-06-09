<?php
require_once('vendor/autoload.php');

session_start();

$routes = [
    'GET' => [


        // Objet
        '/api/v1/objet/getAll/{userId}' => 'ObjetController@getAllByUserId',

        // Proprietaire
        '/api/v1/proprietaire/getAll' => 'ProprietaireController@getAllProprietaires',

        // Categorie
        '/api/v1/categorie/getAll' => 'CategorieController@getAllCategories',


    ],
    'POST' => [
        // Objet
        '/api/v1/objet/addNewObjet' => 'ObjetController@addNewObjet',

    ],
    'PUT' => [
        '/api/v1/users/{id}' => 'UserController@update',


    ],
    'DELETE' => [

    ],
];

const SERVER_ROOT = __DIR__;
const SITE_HASH = 'j7sx88tcgp*98*-OOO_cxnj';

$siteConf = \MyCollection\app\utils\SiteIniFile::instance(SERVER_ROOT . "/secret/dbb_DEV.ini");

$config = new \MiniPhpRest\core\MiniPhpRestConfig();
$config->setIsDebug(true);
$config->setServerRootPath(SERVER_ROOT);
$config->setAppClassFolders([
    'MyCollection' => 'php'
]);
$config->setUriSliceOffset($siteConf->getValue('miniPhpFw', 'uriSliceOffset', 2));

\MiniPhpRest\Runner::followRoute($routes, $config);
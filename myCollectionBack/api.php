<?php
require_once ('vendor/autoload.php');

session_start();

$routes = [
    'GET' => [

        // User
        '/api/v1/test' => 'UserController@testResponse',
        '/api/v1/auth/exists/{userAndId}' => 'JoueurController@isUserAndIdExists',

        // Objet
        '/api/v1/objet/getAll/{userId}' => 'ObjetController@getAllByUserId',
        '/api/v1/joueur/{id}' => 'JoueurController@getJoueurById',


        // Jeu
        '/api/v1/jeux/getAll' => 'JeuController@getAllJeux',
        '/api/v1/jeux/get/{idJeu}' => 'JeuController@getJeuById',
        '/api/v1/jeux/getAllEnigmesByIdJeu/{idJeu}' => 'JeuController@getAllEnigmesByIdJeu',

        // Partie
        '/api/v1/partie/getAllByIdJoueur/{idJoueur}' => 'PartieController@getAllPartiesByIdJoueur',
        '/api/v1/partie/getById/{partieId}' => 'PartieController@getById',
        '/api/v1/enigme/getEnigmeEnCoursById/{enigmeEnCoursId}' => 'PartieController@getEnigmeEnCoursById',


        // Enigme
        '/api/v1/enigme/getById/{enigmeId}' => 'EnigmeController@getEnigmeById',

        // TEST
        '/api/v1/test/getTest/{enigmeEnCoursId}' => 'PartieController@deleteEnigmeEnCoursProps',


    ],
    'POST' => [
        // User
        '/api/v1/auth/register' => 'JoueurController@register',
        '/api/v1/auth/validate' => 'JoueurController@validate',
        '/api/v1/auth/login' => 'JoueurController@login',

        // Partie
        '/api/v1/partie/createNew' => 'PartieController@createNewParty',
        '/api/v1/enigme/setEnigmeEnCoursSolved' => 'PartieController@setEnigmeEnCoursSolved',

        // Enigme
        '/api/v1/enigme/create' => 'EnigmeController@createEnigme',

    ],
    'PUT' => [
        '/api/v1/users/{id}' => 'UserController@update',

        // Enigme
        '/api/v1/enigme/update/{enigmeId}' => 'EnigmeController@updateEnigme',
    ],
    'DELETE' => [
        '/api/v1/users/{id}' => 'UserController@removeUserById',

        '/api/v1/enigme/prop/{idProp}' => 'EnigmeController@deleteEnigmePropById',
    ],
];

const SERVER_ROOT = __DIR__;
const SITE_HASH = 'j7sx88tcgp*98*-OOO_cxnj';

$siteConf = \MyCollection\app\utils\SiteIniFile::instance(SERVER_ROOT."/secret/dbb_DEV.ini");

$config = new \MiniPhpRest\core\MiniPhpRestConfig();
$config->setIsDebug(true);
$config->setServerRootPath(SERVER_ROOT);
$config->setAppClassFolders([
    'MyCollection' => 'php'
]);
$config->setUriSliceOffset($siteConf->getValue('miniPhpFw', 'uriSliceOffset', 2));

\MiniPhpRest\Runner::followRoute($routes, $config);
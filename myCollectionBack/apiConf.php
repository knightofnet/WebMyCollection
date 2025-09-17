<?php
$routes = [
    'GET' => [
        // Auth
        '/api/v1/auth/isAuthenticated' => 'AuthController@isAuthenticated',

        // Objet
        '/api/v1/objet/getAll/{userId}' => 'ObjetController@getAllByUserId',
        '/api/v1/objet/getById/{objetId}' => 'ObjetController@getObjetById',
        '/api/v1/objet/getNamesLike/{namePart}' => 'ObjetController@getObjetNamesLike',
        '/api/v1/objet/getLastAddedObject/{nbLast}' => 'ObjetController@getLastAddedObject',

        // Proprietaire
        '/api/v1/proprietaire/getAll' => 'ProprietaireController@getAllProprietaires',

        // Categorie
        '/api/v1/categorie/getAll' => 'CategorieController@getAllCategories',
        '/api/v1/categorie/getLasts/{nbLast}' => 'CategorieController@getLastCategories',


    ],
    'POST' => [
        // Auth
        '/api/v1/auth/login' => 'AuthController@login',
        '/api/v1/auth/validateToken' => 'AuthController@validateToken',

        // Objet
        '/api/v1/objet/addNewObjet' => 'ObjetController@addNewObjet',
        '/api/v1/objet/addMediaForObjet' => 'ObjetController@addMediaForObjet',
        '/api/v1/objet/importCsv' => 'ObjetController@importFromCsv',

    ],
    'PUT' => [
        '/api/v1/users/{id}' => 'UserController@update',

        // Objet
        '/api/v1/objet/updateObjet' => 'ObjetController@updateObjet',


    ],
    'DELETE' => [

        // Objet
        '/api/v1/objet/deleteObjet' => 'ObjetController@deleteObjet',

        // Media
        '/api/v1/objet/deleteMediaForObjet' => 'ObjetController@deleteMediaForObjet',

    ],
];
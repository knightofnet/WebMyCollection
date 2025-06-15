<?php
$routes = [
    'GET' => [
        // Auth
        '/api/v1/auth/isAuthenticated' => 'AuthController@isAuthenticated',

        // Objet
        '/api/v1/objet/getAll/{userId}' => 'ObjetController@getAllByUserId',
        '/api/v1/objet/getById/{objetId}' => 'ObjetController@getObjetById',

        // Proprietaire
        '/api/v1/proprietaire/getAll' => 'ProprietaireController@getAllProprietaires',

        // Categorie
        '/api/v1/categorie/getAll' => 'CategorieController@getAllCategories',


    ],
    'POST' => [
        // Auth
        '/api/v1/auth/login' => 'AuthController@login',
        '/api/v1/auth/validateToken' => 'AuthController@validateToken',

        // Objet
        '/api/v1/objet/addNewObjet' => 'ObjetController@addNewObjet',

    ],
    'PUT' => [
        '/api/v1/users/{id}' => 'UserController@update',

        // Objet
        '/api/v1/objet/updateObjet' => 'ObjetController@updateObjet',




    ],
    'DELETE' => [

        // Objet
        '/api/v1/objet/deleteObjet' => 'ObjetController@deleteObjet',

    ],
];
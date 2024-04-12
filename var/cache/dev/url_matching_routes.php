<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/albums' => [
            [['_route' => 'album_index', '_controller' => 'App\\Controller\\AlbumController::index'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'album_show', '_controller' => 'App\\Controller\\AlbumController::show'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'album_update', '_controller' => 'App\\Controller\\AlbumController::update'], null, ['PUT' => 0], null, false, false, null],
        ],
        '/artist' => [
            [['_route' => 'artist_index', '_controller' => 'App\\Controller\\ArtistController::show'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'artist_create', '_controller' => 'App\\Controller\\ArtistController::create'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'artist_update', '_controller' => 'App\\Controller\\ArtistController::update'], null, ['PUT' => 0], null, false, false, null],
            [['_route' => 'artist_delete', '_controller' => 'App\\Controller\\ArtistController::delete'], null, ['DELETE' => 0], null, false, false, null],
        ],
        '/' => [[['_route' => 'index', '_controller' => 'App\\Controller\\IndexController::index'], null, null, null, false, false, null]],
        '/donnees' => [[['_route' => 'donnees', '_controller' => 'App\\Controller\\IndexController::donnees'], null, null, null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\LoginController::login'], null, ['POST' => 0], null, false, false, null]],
        '/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\RegistrationController::register'], null, ['POST' => 0], null, false, false, null]],
        '/songs' => [
            [['_route' => 'create_song', '_controller' => 'App\\Controller\\SongController::create'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'get_songs', '_controller' => 'App\\Controller\\SongController::getAll'], null, ['GET' => 0], null, false, false, null],
        ],
        '/user' => [
            [['_route' => 'user_post', '_controller' => 'App\\Controller\\UserController::create'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'user_put', '_controller' => 'App\\Controller\\UserController::update'], null, ['PUT' => 0], null, false, false, null],
            [['_route' => 'user_delete', '_controller' => 'App\\Controller\\UserController::delete'], null, ['DELETE' => 0], null, false, false, null],
            [['_route' => 'user_get', '_controller' => 'App\\Controller\\UserController::read'], null, ['GET' => 0], null, false, false, null],
        ],
        '/user/all' => [[['_route' => 'user_get_all', '_controller' => 'App\\Controller\\UserController::readAll'], null, ['GET' => 0], null, false, false, null]],
        '/api/login_check' => [[['_route' => 'api_login_check'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/songs/([^/]++)(?'
                    .'|(*:60)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        60 => [
            [['_route' => 'get_song', '_controller' => 'App\\Controller\\SongController::get'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'update_song', '_controller' => 'App\\Controller\\SongController::update'], ['id'], ['PUT' => 0], null, false, true, null],
            [['_route' => 'delete_song', '_controller' => 'App\\Controller\\SongController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];

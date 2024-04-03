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
            [['_route' => 'artist_index', '_controller' => 'App\\Controller\\ArtistController::show'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'artist_create', '_controller' => 'App\\Controller\\ArtistController::create'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'artist_update', '_controller' => 'App\\Controller\\ArtistController::update'], null, ['PUT' => 0], null, false, false, null],
            [['_route' => 'artist_delete', '_controller' => 'App\\Controller\\ArtistController::delete'], null, ['DELETE' => 0], null, false, false, null],
        ],
        '/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\RegistrationController::register'], null, null, null, false, false, null]],
        '/login' => [
            [['_route' => 'app_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, null, null, false, false, null],
            [['_route' => 'login', '_controller' => 'App\\Controller\\UserController::login'], null, ['POST' => 0], null, false, false, null],
        ],
        '/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, null, null, false, false, null]],
        '/signup' => [[['_route' => 'app_signup', '_controller' => 'App\\Controller\\SignupController::signUp'], null, ['POST' => 0], null, false, false, null]],
        '/songs' => [
            [['_route' => 'create_song', '_controller' => 'App\\Controller\\SongController::create'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'get_songs', '_controller' => 'App\\Controller\\SongController::getAll'], null, ['GET' => 0], null, false, false, null],
        ],
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

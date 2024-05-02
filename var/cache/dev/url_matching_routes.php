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
            [['_route' => 'app_get_all_albums', '_controller' => 'App\\Controller\\AlbumController::AllAlbums'], null, ['GET' => 0], null, false, false, null],
        ],
        '/artist' => [
            [['_route' => 'app_create_update_artist', '_controller' => 'App\\Controller\\ArtistController::create_update_artist'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'app_get_artists', '_controller' => 'App\\Controller\\ArtistController::getAllArtists'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_delete_artist', '_controller' => 'App\\Controller\\ArtistController::delete'], null, ['DELETE' => 0], null, false, false, null],
        ],
        '/label' => [[['_route' => 'app_create_label', '_controller' => 'App\\Controller\\LabelController::createLabel'], null, ['POST' => 0], null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\LoginController::login'], null, ['POST' => 0], null, false, false, null]],
        '/register' => [[['_route' => 'app_create_user', '_controller' => 'App\\Controller\\RegistrationController::register'], null, ['POST' => 0], null, false, false, null]],
        '/songs' => [
            [['_route' => 'create_song', '_controller' => 'App\\Controller\\SongController::create'], null, ['POST' => 0], null, false, false, null],
            [['_route' => 'get_songs', '_controller' => 'App\\Controller\\SongController::getAll'], null, ['GET' => 0], null, false, false, null],
        ],
        '/user' => [[['_route' => 'app_update_user', '_controller' => 'App\\Controller\\UserController::updateUser'], null, ['POST' => 0], null, false, false, null]],
        '/account-deactivation' => [[['_route' => 'app_delete_user', '_controller' => 'App\\Controller\\UserController::deleteUser'], null, ['DELETE' => 0], null, false, false, null]],
        '/password-lost' => [[['_route' => 'app_reset_password', '_controller' => 'App\\Controller\\UserController::resetPassword'], null, ['POST' => 0], null, false, false, null]],
        '/login_check' => [[['_route' => 'api_login_check'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/artist/([^/]++)(*:58)'
                .'|/label/(?'
                    .'|([^/]++)(?'
                        .'|(*:86)'
                    .')'
                    .'|all(*:97)'
                    .'|([^/]++)(*:112)'
                .')'
                .'|/songs/([^/]++)(?'
                    .'|(*:139)'
                .')'
                .'|/reset\\-password/([^/]++)(*:173)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        58 => [[['_route' => 'app_get_artist_by_fullname', '_controller' => 'App\\Controller\\ArtistController::getArtistByFullname'], ['fullname'], ['GET' => 0], null, false, true, null]],
        86 => [
            [['_route' => 'app_update_label', '_controller' => 'App\\Controller\\LabelController::updateLabel'], ['id'], ['PUT' => 0], null, false, true, null],
            [['_route' => 'app_delete_label', '_controller' => 'App\\Controller\\LabelController::deleteLabel'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        97 => [[['_route' => 'app_get_all_labels', '_controller' => 'App\\Controller\\LabelController::getAllLabels'], [], ['GET' => 0], null, false, false, null]],
        112 => [[['_route' => 'app_get_label_by_id', '_controller' => 'App\\Controller\\LabelController::getLabelById'], ['id'], ['GET' => 0], null, false, true, null]],
        139 => [
            [['_route' => 'get_song', '_controller' => 'App\\Controller\\SongController::get'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'update_song', '_controller' => 'App\\Controller\\SongController::update'], ['id'], ['PUT' => 0], null, false, true, null],
            [['_route' => 'delete_song', '_controller' => 'App\\Controller\\SongController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        173 => [
            [['_route' => 'app_reset_password_post', '_controller' => 'App\\Controller\\UserController::resetPasswordPost'], ['token'], ['POST' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];

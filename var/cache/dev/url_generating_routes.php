<?php

// This file has been auto-generated by the Symfony Routing Component.

return [
    '_preview_error' => [['code', '_format'], ['_controller' => 'error_controller::preview', '_format' => 'html'], ['code' => '\\d+'], [['variable', '.', '[^/]++', '_format', true], ['variable', '/', '\\d+', 'code', true], ['text', '/_error']], [], [], []],
    'album_index' => [[], ['_controller' => 'App\\Controller\\AlbumController::index'], [], [['text', '/albums']], [], [], []],
    'artist_index' => [[], ['_controller' => 'App\\Controller\\ArtistController::show'], [], [['text', '/albums']], [], [], []],
    'artist_create' => [[], ['_controller' => 'App\\Controller\\ArtistController::create'], [], [['text', '/albums']], [], [], []],
    'artist_update' => [[], ['_controller' => 'App\\Controller\\ArtistController::update'], [], [['text', '/albums']], [], [], []],
    'artist_delete' => [[], ['_controller' => 'App\\Controller\\ArtistController::delete'], [], [['text', '/albums']], [], [], []],
<<<<<<< HEAD
    'app_login' => [[], ['_controller' => 'App\\Controller\\LoginController::index'], [], [['text', '/login']], [], [], []],
    'app_login_post' => [[], ['_controller' => 'App\\Controller\\LoginController::login'], [], [['text', '/login']], [], [], []],
    'app_signup' => [[], ['_controller' => 'App\\Controller\\SignupController::signUp'], [], [['text', '/signup']], [], [], []],
=======
    'app_login' => [[], ['_controller' => 'App\\Controller\\LoginController::login'], [], [['text', '/login']], [], [], []],
    'app_register' => [[], ['_controller' => 'App\\Controller\\RegistrationController::register'], [], [['text', '/register']], [], [], []],
>>>>>>> 83da67b534bf387b12d5adebe5ccf17ba866f8d0
    'create_song' => [[], ['_controller' => 'App\\Controller\\SongController::create'], [], [['text', '/songs']], [], [], []],
    'get_song' => [['id'], ['_controller' => 'App\\Controller\\SongController::get'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/songs']], [], [], []],
    'get_songs' => [[], ['_controller' => 'App\\Controller\\SongController::getAll'], [], [['text', '/songs']], [], [], []],
    'update_song' => [['id'], ['_controller' => 'App\\Controller\\SongController::update'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/songs']], [], [], []],
    'delete_song' => [['id'], ['_controller' => 'App\\Controller\\SongController::delete'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/songs']], [], [], []],
    'user_post' => [[], ['_controller' => 'App\\Controller\\UserController::create'], [], [['text', '/user']], [], [], []],
    'user_put' => [[], ['_controller' => 'App\\Controller\\UserController::update'], [], [['text', '/user']], [], [], []],
    'user_delete' => [[], ['_controller' => 'App\\Controller\\UserController::delete'], [], [['text', '/user']], [], [], []],
    'user_get' => [[], ['_controller' => 'App\\Controller\\UserController::read'], [], [['text', '/user']], [], [], []],
    'user_get_all' => [[], ['_controller' => 'App\\Controller\\UserController::readAll'], [], [['text', '/user/all']], [], [], []],
<<<<<<< HEAD
=======
    'api_login_check' => [[], [], [], [['text', '/api/login_check']], [], [], []],
>>>>>>> 83da67b534bf387b12d5adebe5ccf17ba866f8d0
    'App\Controller\AlbumController::index' => [[], ['_controller' => 'App\\Controller\\AlbumController::index'], [], [['text', '/albums']], [], [], []],
    'App\Controller\ArtistController::index' => [[], ['_controller' => 'App\\Controller\\ArtistController::show'], [], [['text', '/albums']], [], [], []],
    'App\Controller\ArtistController::create' => [[], ['_controller' => 'App\\Controller\\ArtistController::create'], [], [['text', '/albums']], [], [], []],
    'App\Controller\ArtistController::update' => [[], ['_controller' => 'App\\Controller\\ArtistController::update'], [], [['text', '/albums']], [], [], []],
    'App\Controller\ArtistController::delete' => [[], ['_controller' => 'App\\Controller\\ArtistController::delete'], [], [['text', '/albums']], [], [], []],
<<<<<<< HEAD
    'App\Controller\LoginController::index' => [[], ['_controller' => 'App\\Controller\\LoginController::index'], [], [['text', '/login']], [], [], []],
    'App\Controller\LoginController::login' => [[], ['_controller' => 'App\\Controller\\LoginController::login'], [], [['text', '/login']], [], [], []],
    'App\Controller\SignupController::signUp' => [[], ['_controller' => 'App\\Controller\\SignupController::signUp'], [], [['text', '/signup']], [], [], []],
=======
    'App\Controller\LoginController::login' => [[], ['_controller' => 'App\\Controller\\LoginController::login'], [], [['text', '/login']], [], [], []],
    'App\Controller\RegistrationController::register' => [[], ['_controller' => 'App\\Controller\\RegistrationController::register'], [], [['text', '/register']], [], [], []],
>>>>>>> 83da67b534bf387b12d5adebe5ccf17ba866f8d0
    'App\Controller\SongController::create' => [[], ['_controller' => 'App\\Controller\\SongController::create'], [], [['text', '/songs']], [], [], []],
    'App\Controller\SongController::get' => [['id'], ['_controller' => 'App\\Controller\\SongController::get'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/songs']], [], [], []],
    'App\Controller\SongController::getAll' => [[], ['_controller' => 'App\\Controller\\SongController::getAll'], [], [['text', '/songs']], [], [], []],
    'App\Controller\SongController::update' => [['id'], ['_controller' => 'App\\Controller\\SongController::update'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/songs']], [], [], []],
    'App\Controller\SongController::delete' => [['id'], ['_controller' => 'App\\Controller\\SongController::delete'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/songs']], [], [], []],
    'App\Controller\UserController::create' => [[], ['_controller' => 'App\\Controller\\UserController::create'], [], [['text', '/user']], [], [], []],
    'App\Controller\UserController::update' => [[], ['_controller' => 'App\\Controller\\UserController::update'], [], [['text', '/user']], [], [], []],
    'App\Controller\UserController::delete' => [[], ['_controller' => 'App\\Controller\\UserController::delete'], [], [['text', '/user']], [], [], []],
    'App\Controller\UserController::read' => [[], ['_controller' => 'App\\Controller\\UserController::read'], [], [['text', '/user']], [], [], []],
    'App\Controller\UserController::readAll' => [[], ['_controller' => 'App\\Controller\\UserController::readAll'], [], [['text', '/user/all']], [], [], []],
];

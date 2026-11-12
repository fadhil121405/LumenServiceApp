<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return response()->json([
        'service_name' => 'PHP Service App',
        'status' => 'Running',
    ]);
});

$router->get('/hello-lumen', function () {
    return "<h1>Lumen</h1><p>Hi good developer, thank for using Lumen</p>";
});

$router->get('/hello-lumen/{name}', function ($name) {
    return "<h1>Lumen</h1><p>Hi <b>" . $name . "</b>, thank for using Lumen</p>";
});

$router->get('/scores', [
    'middleware' => 'login',
    function () {
        return "<h1>Selamat</h1><p>Nilai anda 100</p>";
    }
]);

$router->get('users', 'UsersController@index');
$router->get('users/{userId}', 'UsersController@show');

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/posts', 'PostsController@index');
    $router->post('/posts', 'PostsController@store');
    $router->get('/post/{id}', 'PostsController@show');
    $router->put('/post/{id}', 'PostsController@update');
    $router->delete('/post/{id}', 'PostsController@destroy');
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/products', 'ProductsController@index');
    $router->post('/products', 'ProductsController@store');
    $router->get('/products/{productId}', 'ProductsController@show');
    $router->put('/products/{productId}', 'ProductsController@update');
    $router->delete('/products/{productId}', 'ProductsController@destroy');
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->delete('/auth/logout', 'AuthController@logout');
});
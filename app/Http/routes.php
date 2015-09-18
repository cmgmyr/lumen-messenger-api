<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() use ($app) {
    return $app->welcome();
});

$app->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'api/v1/messages'], function () use ($app) {
    $app->get('/', ['as' => 'messages', 'uses' => 'MessagesController@index']);
    $app->post('/', ['as' => 'messages.store', 'uses' => 'MessagesController@store']);
    $app->get('/{id}', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
    $app->put('/{id}', ['as' => 'messages.update', 'uses' => 'MessagesController@update']);
});

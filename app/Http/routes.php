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

$app->group(['namespace' => 'App\Http\Controllers'], function () use ($app) {
    $app->get('api/v1/messages', ['as' => 'messages', 'uses' => 'MessagesController@index']);
    $app->get('api/v1/messages/create', ['as' => 'messages.create', 'uses' => 'MessagesController@create']);
    $app->post('api/v1/messages', ['as' => 'messages.store', 'uses' => 'MessagesController@store']);
    $app->get('api/v1/messages/{id}', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
    $app->put('api/v1/messages/{id}', ['as' => 'messages.update', 'uses' => 'MessagesController@update']);
});

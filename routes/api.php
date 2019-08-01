<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Основной маршрут
Route::post('/hook', 'HookController@main');

// Получение данных по идентификатору
Route::get('/container/{id}', 'HookController@getById')->where('id', '[0-9]+');

// Получение списка контейнеров с уникальными товарами
Route::get('/containers-with-unique-items', 'HookController@getUnique');


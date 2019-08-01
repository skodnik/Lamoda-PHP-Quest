<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Models\Container;
use App\Models\Item;
use App\Models\Name;

Route::get('/', function () {
    return view('welcome');
});

// Основной маршрут
Route::post('/hook', 'HookController@main');

// Получение данных по идентификатору
Route::get('/container/{id}', 'HookController@getById')->where('id', '[0-9]+');

// Получение списка контейнеров с уникальными товарами
Route::get('/containers-with-unique-items', 'HookController@getUnique');

// Заглушка для hook
Route::get('/hook', function () {
    abort(404);
});

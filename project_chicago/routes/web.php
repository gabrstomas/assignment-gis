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

Route::get('/', 'PageController@homepage');
Route::get('/neighborhoods', 'PageController@neighborhoods');
Route::get('/schools', 'PageController@schools');
Route::get('/stations', 'PageController@stations');
Route::get('/explore', 'PageController@explore');
Route::get('/search', 'PageController@search');

Route::get('/test', 'TestController@test');

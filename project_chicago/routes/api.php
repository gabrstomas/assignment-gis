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

Route::get('/schools', 'TestController@schools');
Route::get('/schoolsf', 'TestController@schoolsAsFeatures');
Route::get('/schooldist', 'TestController@schoolsWithDistances');
Route::get('/stations', 'TestController@stationsAsFeatures');
Route::get('/stationcrimes', 'TestController@stationsWithCrimes');
Route::get('/crimes', 'TestController@crimesAsFeatures');
Route::get('/neighborhoods', 'TestController@neighborhoods');
Route::get('/neighborhoodcrimes', 'TestController@neighborhoodsWithCrimes');

Route::get('/all_schools', 'MapController@allSchools');
Route::get('/search', 'MapController@search');
Route::get('/neighborhoods', 'MapController@neighborhoods');
Route::get('/schools', 'MapController@schools');
Route::get('/items_in_neighborhood', 'MapController@itemsInNeighborhood');
Route::get('/stations', 'MapController@stations');
Route::get('/station_for_school', 'MapController@closestStationForSchool');
Route::get('/stations_for_school', 'MapController@stationsForSchool');
Route::get('/crimes_for_station', 'MapController@crimesForStation');
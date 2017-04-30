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

Route::get('/', function () {
    return view('home');
});

Route::get('/api/projects', 'ProjectController@get_projects');
Route::get('/api/project/{slug}', 'ProjectController@get_project');

// usage items
Route::get('/api/project/{slug}/usage/{type?}', 'DataController@usage')
      ->where(['type' => 'hdd|ram|(t|r)x']);

Route::get('/api/project/{slug}/plugins', 'DataController@plugins');
Route::get('/api/project/{slug}/tables/{table?}', 'DataController@tables');

Route::post('/api/project/{slug}/key', 'ProjectController@set_key');
Route::post('/api/add', 'ProjectController@add_project');

// External connections
Route::post('/api/collect', 'FetchDataController@collect');

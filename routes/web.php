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

/*
  API used by Javascript
*/
Route::get('/api/project/{slug}/usage/{type?}', 'DataController@usage')
      ->where(['type' => 'hdd|ram|(t|r)x|page|cpu']);

// Get all plugins
Route::get('/api/project/{slug}/plugins', 'DataController@plugins');

// Get all tables, or if table is given, data for specific table
Route::get('/api/project/{slug}/tables/{table?}', 'DataController@tables');

// Get status, with optional from date
Route::get('/api/project/{slug}/status/{date?}', 'DataController@status');
Route::get('/api/project/{slug}/events', 'DataController@events');

// Return CSV
Route::get('/api/project/{slug}/csv/usage', 'CSVController@usage');

Route::post('/api/project/{slug}/key', 'ProjectController@set_key');
Route::post('/api/add', 'ProjectController@add_project');

// External connections
Route::post('/api/collect', 'FetchDataController@collect');

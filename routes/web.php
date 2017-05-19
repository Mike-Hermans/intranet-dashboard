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

// Get the most recent events for a project
Route::get('/api/project/{slug}/events', 'DataController@events');

// Get the forecast for a type
Route::get('/api/project/{slug}/forecast/{type}', 'ForecastController@get_forecast')
      ->where(['type' => 'hdd|ram|(t|r)x|page|cpu']);

// Return CSV
Route::get('/csv/{slug}', 'CSVController@usage');

// Start the forecast for a given project
Route::get('/forecast/{slug}/{type}', 'ForecastController@forecast')
      ->where(['type' => 'hdd|ram|(t|r)x|page|cpu']);


Route::post('/api/project/{slug}/key', 'ProjectController@set_key');
Route::post('/api/add', 'ProjectController@add_project');

// External connections
Route::post('/api/collect', 'FetchDataController@collect');

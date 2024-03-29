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

/*
 * Projects API
 */
Route::get('/api/projects', 'ProjectController@getProjects');
Route::get('/api/project/{slug}', 'ProjectController@getProject');

Route::get('/api/project/{slug}/usage/{type?}', 'DataController@usage')
      ->where(['type' => 'hdd|ram|page|cpu']);

Route::get('/api/project/{slug}/lastusage', 'DataController@lastUsage');
Route::get('/api/project/{slug}/latest', 'DataController@latest');

// Get all plugins
Route::get('/api/project/{slug}/plugins', 'DataController@plugins');

// Get all tables, or if table is given, data for specific table
Route::get('/api/project/{slug}/tables/{table?}', 'DataController@tables');

// Get status, with optional from date
Route::get('/api/project/{slug}/status/{date?}', 'DataController@status');

// Get the most recent events for a project
Route::get('/api/project/{slug}/events', 'DataController@events');

// Get Notes
Route::get('/api/project/{slug}/notes', 'DataController@getNotes');

Route::get('/api/project/{slug}/isworking', 'DataController@isWorking');

// Get the forecast for a type
Route::get('/api/project/{slug}/forecast/{type}', 'DataController@getForecast');
Route::get('/api/project/{slug}/createforecasts', 'ForecastController@forecast');

/*
 * Neural Network API
 */
Route::get('/api/network/nnstats', 'NNController@NNStats');
Route::get('/api/network/projects', 'NNController@projects');
// Return CSV
Route::get('/csv/{slug}', 'CSVController@usage');
// Return a bigger set
Route::get('/csvml', 'CSVController@prepareMl');

// Start the forecast for a given project
Route::get('/forecast/{slug}/{type}', 'ForecastController@forecast')
      ->where(['type' => 'hdd|ram|page|cpu']);

/*
 * Project management via frontend
 */
Route::post('/api/add', 'ProjectController@addProject');
Route::post('/api/slug', 'ProjectController@createSlug');
Route::post('/api/project/{slug}/notes', 'FetchDataController@saveNotes');
Route::post('/api/project/{slug}/update', 'ProjectController@updateProject');
Route::post('/api/project/remove', 'ProjectController@removeProject');

/*
 * External connections
 */
Route::post('/api/collect', 'FetchDataController@collect');

Route::get('/nn', 'NNController@log');

//Route::get('/cleanup', 'DataController@dataCleanup');

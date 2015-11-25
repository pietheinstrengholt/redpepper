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

Route::get('/', function () {
    return view('index');
});

// Provide controller methods with object instead of ID
Route::model('sections', 'Section');
Route::model('templates', 'Template');
Route::model('sources', 'TechnicalSource');
Route::model('types', 'TechnicalType');
Route::model('changerequests', 'ChangeRequest');
 
// Use IDs in URLs
Route::bind('sections', function($value, $route) {
	return App\Section::whereId($value)->first();
});

Route::bind('templates', function($value, $route) {
	return App\Template::whereId($value)->first();
});

Route::bind('sources', function($value, $route) {
	return App\TechnicalSource::whereId($value)->first();
});

Route::bind('types', function($value, $route) {
	return App\TechnicalType::whereId($value)->first();
});

Route::bind('changerequests', function($value, $route) {
	return App\ChangeRequest::whereId($value)->first();
});
 
Route::resource('sections', 'SectionController');
Route::resource('sections.templates', 'TemplateController');
Route::resource('sources', 'TechnicalSourceController');
Route::resource('types', 'TechnicalTypeController');
Route::resource('changerequests', 'ChangeRequestController');

//getCellContent api call
Route::get('/cell', 'TemplateController@getCellContent');
Route::get('/updatecell', 'ChangeRequestController@create');
Route::post('/updatecell', 'ChangeRequestController@submit');

Route::get('exporttemplate/{id}', 'TemplateController@export');

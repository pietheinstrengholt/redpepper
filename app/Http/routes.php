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

Route::get('/home', function () {
    return view('index');
});

// Provide controller methods with object instead of ID
Route::model('sections', 'Section');
Route::model('templates', 'Template');
Route::model('sources', 'TechnicalSource');
Route::model('types', 'TechnicalType');
Route::model('departments', 'Department');
Route::model('users', 'User');
Route::model('changerequests', 'ChangeRequest');
Route::model('logs', 'Log');
Route::model('terms', 'Term');
Route::model('subjects', 'Subject');
Route::model('fileupload', 'FileUpload');

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

Route::bind('departments', function($value, $route) {
	return App\Department::whereId($value)->first();
});

Route::bind('users', function($value, $route) {
	return App\User::whereId($value)->first();
});

Route::bind('changerequests', function($value, $route) {
	return App\ChangeRequest::whereId($value)->first();
});

Route::bind('logs', function($value, $route) {
	return App\Log::whereId($value)->first();
});

Route::bind('terms', function($value, $route) {
	return App\Term::whereId($value)->first();
});

Route::bind('subjects', function($value, $route) {
	return App\Subject::whereId($value)->first();
});

Route::bind('fileupload', function($value, $route) {
	return App\FileUpload::whereId($value)->first();
});

// User routes...
Route::get('users/{id}/rights', ['middleware' => 'auth', 'uses' => 'UserController@rights']);
Route::get('users/{id}/password', ['middleware' => 'auth', 'uses' => 'UserController@password']);
Route::post('updaterights', ['middleware' => 'auth', 'uses' => 'UserController@updaterights']);
Route::post('updatepassword', ['middleware' => 'auth', 'uses' => 'UserController@updatepassword']);

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

// Template routes...
Route::get('templatemanual/{id}', 'TemplateController@manual');
Route::get('templatestructure/{id}', ['middleware' => 'auth', 'uses' => 'TemplateController@structure']);
Route::post('changestructure', ['middleware' => 'auth', 'uses' => 'TemplateController@changestructure']);
Route::post('newtemplate', ['middleware' => 'auth', 'uses' => 'TemplateController@newtemplate']);

// Model routes...
Route::resource('sections', 'SectionController');
Route::resource('sections.templates', 'TemplateController');
Route::resource('sources', 'TechnicalSourceController');
Route::resource('types', 'TechnicalTypeController');
Route::resource('departments', 'DepartmentController');
Route::resource('users', 'UserController');
Route::resource('changerequests', 'ChangeRequestController');
Route::resource('logs', 'LogController');
Route::resource('terms', 'TermController');
Route::resource('subjects', 'SubjectController');
Route::resource('fileupload', 'FileUploadController');

// Settings
Route::get('settings', ['middleware' => 'auth', 'uses' => 'SettingController@index']);
Route::post('/settings', 'SettingController@store');

//getCellContent api call
Route::get('/cell', 'TemplateController@getCellContent');
Route::get('/updatecell', 'ChangeRequestController@create');
Route::post('/updatecell', 'ChangeRequestController@submit');

// Excel routes...
Route::get('exporttemplate/{id}', 'ExcelController@export');
Route::get('uploadtemplate', ['middleware' => 'auth', 'uses' => 'ExcelController@upload']);
Route::get('/excel/uploadtemplate', ['middleware' => 'auth', 'uses' => 'ExcelController@uploadtemplateform']);
Route::get('/excel/uploadreference', ['middleware' => 'auth', 'uses' => 'ExcelController@uploadreferenceform']);
Route::post('/excel/uploadexcel', ['middleware' => 'auth', 'uses' => 'ExcelController@uploadtemplateexcel']);
Route::post('/excel/uploadreference', ['middleware' => 'auth', 'uses' => 'ExcelController@uploadreferenceexcel']);

// CSV routes...
Route::get('/csv/import', ['middleware' => 'auth', 'uses' => 'CSVController@import']);
Route::get('/csv/seeids', ['middleware' => 'auth', 'uses' => 'CSVController@seeids']);
Route::post('/csv/uploadcsv', ['middleware' => 'auth', 'uses' => 'CSVController@uploadcsv']);

// Changerequest routes...
Route::post('/changerequests/uploadexcel', ['middleware' => 'auth', 'uses' => 'ChangeRequestController@update']);
Route::post('/changerequests/cleanup', ['middleware' => 'auth', 'uses' => 'ChangeRequestController@cleanup']);
Route::post('/changerequests/exportchanges', ['middleware' => 'auth', 'uses' => 'ExcelController@exportchanges']);

// Search routes...
Route::post('/search', 'SearchController@search');
Route::get('/search', 'SearchController@search');
Route::get('/advancedsearch', 'SearchController@advancedsearch');

// Authentication routes...
Route::auth();
Route::get('/register', 'Auth\AuthController@getRegister');
Route::post('/login', 'Auth\AuthController@postLogin');
//Route::post('register', 'Auth\AuthController@postRegister');

//Manuals
Route::get('/manuals', 'SectionController@manuals');
Route::get('/manuals/{id}', 'SectionController@showmanual');

//image upload
Route::get('/imageupload', function() {
	return view('imageupload.image-dialog');
});
Route::post('/imageupload', 'TemplateController@imageUpload');

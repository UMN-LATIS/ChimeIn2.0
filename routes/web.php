<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChimeController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\PresentController;

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

// Home Page Routes
Route::get('/', 'HomeController@index')->middleware('auth');
Route::get('/api/chime', 'HomeController@getChimes')->middleware('auth');
Route::post('/api/chime', 'HomeController@createChime')->middleware('auth');
Route::post('/api/chime/{access_code}',
    'HomeController@joinChime')->middleware('auth');
Route::delete('/api/chime/{chime_id}',
    'HomeController@deleteChime')->middleware('auth');

// Chime Page Routes
Route::get('/chime/{chime_id}', 'ChimeController@index')->middleware('auth');
Route::get('/api/chime/{chime_id}',
    'ChimeController@getChime')->middleware('auth');
Route::get('/api/chime/{chime_id}/response',
    'ChimeController@getPastResponses')->middleware('auth');
Route::get('/api/chime/{chime_id}/image/{image_name}',
    'ChimeController@getImage')->middleware('auth');
Route::post('/api/chime/{chime_id}/image',
    'ChimeController@uploadImage')->middleware('auth');
Route::post('/api/chime/{chime_id}/folder',
    'ChimeController@createFolder')->middleware('auth');
Route::delete('/api/chime/{chime_id}/folder/{folder_id}',
    'ChimeController@deleteFolder')->middleware('auth');

// Response subroutes
Route::get('/api/chime/{chime_id}/session/{session_id}',
    'ResponseController@getResponse')->middleware('auth');
Route::get('/api/chime/{chime_id}/session/{session_id}/question',
    'ResponseController@getQuestion')->middleware('auth');
Route::post('/api/chime/{chime_id}/session/{session_id}',
    'ResponseController@createResponse')->middleware('auth');
Route::put('/api/chime/{chime_id}/session/{session_id}/response/{response_id}',
    'ResponseController@updateResponse')->middleware('auth');

    // Folder Routes (chime page subroutes)
Route::get('/api/chime/{chime_id}/folder/{folder_id}', 
    'FolderController@getQuestions')->middleware('auth');
Route::post('/api/chime/{chime_id}/folder/{folder_id}',
    'FolderController@createQuestion')->middleware('auth');
Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}',
    'FolderController@updateQuestion')->middleware('auth');
Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/move_down',
    'FolderController@moveQuestionDown')->middleware('auth');
Route::delete('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}',
    'FolderController@deleteQuestion')->middleware('auth');

// Presentation Routes
Route::get('/chime/{chime_id}/folder/{folder_id}/present',
    'PresentController@index')->middleware('auth');
Route::get('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}',
    'PresentController@getSessions')->middleware('auth');
Route::get('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/session/{session_id}',
    'PresentController@getResponses')->middleware('auth');
Route::post('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}',
    'PresentController@startSession')->middleware('auth');
Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/session/{session_id}',
    'PresentController@stopSession')->middleware('auth');

Auth::routes();
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

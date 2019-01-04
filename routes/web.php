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
// 
// 

Route::group(['middleware' => ['shibinjection']], function () {
    Route::get('/', 'HomeController@index')->name("home");
    
    Route::model('chime', '\App\Chime');
    Route::model('folder', '\App\Folder');


    Route::get('/api/chime', 'HomeController@getChimes');
    Route::post('/api/chime', 'HomeController@createChime');
    Route::get('/join/{access_code}', 'HomeController@joinChime');
    Route::post('/join/{access_code}', 'HomeController@joinChime');
    Route::delete('/api/chime/{chime_id}', 'HomeController@deleteChime');

    // Chime Page Routes
    Route::get('/api/chime/{chime_id}', 'ChimeController@getChime');
    Route::get('/api/chime/{chime_id}/users', 'ChimeController@getUsers');
    Route::put('/api/chime/{chime}/users', 'ChimeController@syncUsers');
    Route::put('/api/chime/{chime_id}/users/{user_id}', 'ChimeController@changePermission');
    Route::delete('/api/chime/{chime_id}/users/{user_id}', 'ChimeController@removeUser');
    Route::get('/api/chime/{chime_id}/response', 'ChimeController@getPastResponses');
    Route::get('/api/chime/{chime_id}/image/{image_name}', 'ChimeController@getImage');
    Route::post('/api/chime/{chime_id}/image', 'ChimeController@uploadImage');
    Route::post('/api/chime/{chime_id}/folder', 'ChimeController@createFolder');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}', 'ChimeController@editFolder');
    Route::delete('/api/chime/{chime_id}/folder/{folder_id}', 'ChimeController@deleteFolder');


    Route::patch('/api/chime/{chime}', 'ChimeController@updateChime');

    // Response subroutes
    Route::get('/api/chime/{chime_id}/responses', 'ResponseController@getResponse');
    Route::get('/api/chime/{chime_id}/session/{session_id}/question', 'ResponseController@getQuestion');
    Route::put('/api/chime/{chime}/session/{session}/response/{response?}', 'ResponseController@createOrUpdateResponse');

        // Folder Routes (chime page subroutes)
    Route::get('/api/chime/{chime}/folder/{folder}/{includeQuestions?}',  'FolderController@show');
    Route::post('/api/chime/{chime_id}/folder/{folder_id}', 'FolderController@createQuestion');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', 'FolderController@updateQuestion');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}/save_order', 'FolderController@saveOrder');
    Route::delete('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', 'FolderController@deleteQuestion');

    // Presentation Routes
    // Route::get('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', 'PresentController@getSessions');
    // Route::get('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/session/{session_id}', 'PresentController@getResponses');
    Route::post('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', 'PresentController@startSession');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/stopSession', 'PresentController@stopSession');
});
    // Auth::routes();


Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');


if (config('shibboleth.emulate_idp') ) {
    Route::name('login')->get("login", '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateLogin');
    Route::group(['middleware' => 'web'], function () {
        Route::get('emulated/idp', 'StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateIdp');
        Route::post('emulated/idp', 'StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateIdp');
        Route::get('emulated/login', 'StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateLogin');
        Route::get('emulated/logout', 'StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@emulateLogout');
    });
} else {
    Route::name('login')->get("login", '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@login');
    Route::group(['middleware' => 'web'], function () {
        Route::name('shibboleth-login')->get('/shibboleth-login', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@login');
        Route::name('shibboleth-authenticate')->get('/shibboleth-authenticate', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@idpAuthenticate');
        Route::name('shibboleth-logout')->get('/shibboleth-logout', '\StudentAffairsUwm\Shibboleth\Controllers\ShibbolethController@destroy');
    });
}

Route::post('lti', 'LTIHandler@launch');
Route::get('ltiConfig', 'LTIHandler@configInfo');

Route::any('{all}','HomeController@index')->where(['all' => '.*']);
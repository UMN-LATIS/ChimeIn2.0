<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChimeController;
use App\Http\Controllers\FolderParticipationContoller;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\PresentController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\QuestionController;

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

Route::get("/ltiSelectionPromptDemo", function() {
    $chime = \App\Chime::first();
    $chimes = \App\Chime::all();
    return view("ltiSelectionPrompt", ["saveTarget"=>"ltisettings13.update", "ltiLaunch"=>["similar_chimes"=>$chimes], "lti_resource_title"=>"test course", "resource_link_pk"=>"1111", "chime"=>$chime]);
});

Route::group(['middleware' => ['shibinjection']], function () {

    Route::get('/api/users/self', 'UsersController@getCurrentUser');

    Route::get('/', 'HomeController@index')->name("home");
    Route::get('/loginAndRedirect', 'HomeController@loginAndRedirect');
    Route::model('chime', '\App\Chime');
    Route::model('folder', '\App\Folder');


    Route::get('/api/chime', 'ChimeController@getChimes');
    Route::post('/api/chime', 'ChimeController@createChime');
    Route::get('/join/{access_code}', 'ChimeController@joinChime');
    Route::post('/join/{access_code}', 'ChimeController@joinChime');
    Route::delete('/api/chime/{chime_id}', 'ChimeController@deleteChime');

    // Chime Page Routes
    Route::get('/api/chime/{chime_id}', 'ChimeController@getChime');
    Route::get('/api/chime/{chime_id}/users', 'ChimeController@getUsers');
    Route::post('/api/chime/{chime}/sync', 'ChimeController@forceSync');
    Route::put('/api/chime/{chime}/users/{user}', [ChimeController::class, 'updateChimeUser']);
    Route::delete('/api/chime/{chime_id}/users/{user_id}', [ChimeController::class, 'removeChimeUser']);

    Route::get('/api/chime/{chime_id}/response', 'ChimeController@getPastResponses');
    Route::get('/api/chime/{chime_id}/image/{image_name}', 'ChimeController@getImage');
    Route::post('/api/chime/{chime_id}/image', 'ChimeController@uploadImage');
    Route::post('/api/chime/{chime_id}/folder', 'ChimeController@createFolder');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}', 'ChimeController@editFolder');
    Route::delete('/api/chime/{chime_id}/folder/{folder_id}', 'ChimeController@deleteFolder');
    Route::get('/api/chime/{chime}/folder/{folder}/participation', [FolderParticipationContoller::class, 'index']);
    Route::get('/api/chime/{chime}/openQuestions', 'ChimeController@getOpenQuestions');

    Route::patch('/api/chime/{chime}', 'ChimeController@update');
    Route::put('/api/chime/{chime}', 'ChimeController@updateFolders');
    

    Route::post('/api/chime/{chime}/export/', 'ChimeController@exportChime');

    // Response subroutes
    Route::get('/api/chime/{chime_id}/responses', 'ResponseController@getResponse');
    Route::get('/api/chime/{chime_id}/session/{session_id}/question', 'ResponseController@getQuestion');
    Route::put('/api/chime/{chime}/session/{session}/response/{response?}', 'ResponseController@createOrUpdateResponse')
        ->middleware('limit.json.size');

    Route::post('/api/chime/{chime}/folder/{folder}/question/startAll', 'PresentController@startAllQuestions');
    Route::put('/api/chime/{chime}/folder/{folder}/question/stopAll', 'PresentController@stopAllQuestions');

    // Folder Routes (chime page subroutes)
    // TODO: Change `includeQuestions to a query string param 
    // `include_questions=true`
    Route::get('/api/chime/{chime}/folder/{folder}/{includeQuestions?}',  'FolderController@show');
    Route::post('/api/chime/{chime_id}/folder/{folder_id}', [QuestionController::class, 'store'])
        ->middleware('limit.json.size');
    Route::post('/api/chime/{chime}/folder/{folder}/import', 'FolderController@importQuestions');
    Route::post('/api/chime/{chime}/folder/{folder}/sync', 'FolderController@forceSync');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', [QuestionController::class, 'update'])
        ->middleware('limit.json.size');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}/save_order', 'FolderController@saveOrder');
    Route::delete('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', [QuestionController::class, 'destroy']);
    Route::delete('/api/chime/{chime}/folder/{folder}/question/{question}/responses', 'FolderController@resetQuestion');
    
    Route::delete('/api/chime/{chime}/folder/{folder}/response/{response}', 'ResponseController@deleteResponse');

    // Presentation Routes
    // Route::get('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', 'PresentController@getSessions');
    // Route::get('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/session/{session_id}', 'PresentController@getResponses');

    Route::post('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}', 'PresentController@startSession');
    Route::put('/api/chime/{chime_id}/folder/{folder_id}/question/{question_id}/stopSession', 'PresentController@stopSession');
    
    Route::get('/impersonate/stop', [ImpersonateController::class, 'stop'])->name('impersonate.stop');

});
    // Auth::routes();



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
Route::put('lti/saveLTISettings/{chime}', 'LTIHandler@saveLTISettings')->name("ltisettings.update");
Route::get('ltiConfig', 'LTIHandler@configInfo');
Route::post('lti13/login', 'LTI13Handler@login');
Route::post('lti13/launch', 'LTI13Handler@launch');
Route::get('lti13/config', 'LTI13Handler@config');
Route::put('lti13/saveLTISettings/{chime}', 'LTI13Handler@saveLTISettings')->name("ltisettings13.update");
Route::any('{all}','HomeController@index')->where(['all' => '.*']);

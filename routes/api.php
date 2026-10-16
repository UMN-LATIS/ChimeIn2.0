<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Token-authenticated routes for the ChimeIn PowerPoint add-in.
|
| These deliberately sit outside the `web` middleware group: AuthIfNecessary
| manufactures a guest user for unauthenticated session requests, which would
| turn a missing token into a silent success instead of a 401.
|
*/

Route::prefix('office')
    ->name('office.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('me', 'Office\OfficeApiController@me')->name('me');

        Route::middleware('abilities:office:browse')->group(function () {
            Route::get('chimes', 'Office\OfficeApiController@chimes')->name('chimes');
            Route::post('chimes/{chime}/token', 'Office\OfficeApiController@issueChimeToken')->name('chime.token');
        });

        Route::prefix('chimes/{chime}')->group(function () {
            Route::middleware('office.scope:read')->group(function () {
                Route::get('/', 'Office\OfficeApiController@chime')->name('chime');
                Route::get('folders/{folder}', 'Office\OfficeApiController@folder')->name('folder');
                Route::get('users', 'Office\OfficeApiController@users')->name('users');
            });

            Route::middleware('office.scope:results')->group(function () {
                Route::get('questions/{question}', 'Office\OfficeApiController@question')->name('question');
            });

            Route::middleware('office.scope:present')->group(function () {
                Route::post('questions/{question}/open', 'Office\OfficeApiController@open')->name('question.open');
                Route::post('questions/{question}/close', 'Office\OfficeApiController@close')->name('question.close');
            });
        });
    });

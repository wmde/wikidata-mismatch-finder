<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthUserController;

/*
|--------------------------------------------------------------------------
| Authentication routes
|--------------------------------------------------------------------------
|
| Here is where you can register routes that involve stateful (non REST API)
| authorization and authentication. These routes are loaded by the
| RouteServiceProvider within a group which contains the "web" middleware group.
|
*/

Route::get('/login', [OAuthUserController::class, 'login'])
    ->name('login');

Route::get('/callback', [OAuthUserController::class, 'callback'])
    ->name('oauth.callback');

Route::get('/logout', [OAuthUserController::class, 'logout'])
    ->name('logout');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthUserController;
use App\Http\Controllers\ApiTokenController;

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

Route::get('/api-settings', [ApiTokenController::class, 'showToken'])
    ->name('api.settings');

Route::get('/create-token', [ApiTokenController::class, 'createToken'])->middleware('auth')
    ->name('api.create_token');

Route::get('/revoke-token', [ApiTokenController::class, 'revokeToken'])->middleware('auth')
    ->name('api.revoke_token');

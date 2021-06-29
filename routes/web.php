<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthUserController;
use App\Http\Controllers\ApiTokenController;

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
    return view('welcome');
});

Route::get('/login', [OAuthUserController::class, 'login'])
    ->name('login');

Route::get('/callback', [OAuthUserController::class, 'callback'])
    ->name('oauth.callback');

Route::get('/logout', [OAuthUserController::class, 'logout'])
    ->name('logout');

Route::get('/token', [ApiTokenController::class, 'showToken'])->middleware('auth')->name('token');
Route::get('/createToken', [ApiTokenController::class, 'createToken'])->middleware('auth')->name('token.create');
Route::get('/revokeToken', [ApiTokenController::class, 'revokeToken'])->middleware('auth')->name('token.revoke');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuthUserController;

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

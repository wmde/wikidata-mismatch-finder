<?php

use App\Models\ImportMeta;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Development Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for testing the application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "dev" prefix.
|
*/

// Local dev route to test vue components
Route::get('/playground', function () {
    $user = Auth::user() ? [
        'name' => Auth::user()->username
    ] : null;

    return inertia('Playground', [
        'user' => $user
    ]);
})->name('playground');

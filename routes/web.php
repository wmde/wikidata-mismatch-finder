<?php

use App\Models\ImportMeta;
use Illuminate\Support\Facades\Route;

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
    return redirect(route('api.settings'));
});

Route::get('/test', function () {
    return view('home');
});

Route::get('/imports', function () {
    return view('importStatus', [ 'imports' => ImportMeta::with('error')->orderByDesc('id')->take(10)->get() ]);
})->name('import.status');

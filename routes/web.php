<?php

use App\Models\ImportMeta;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RandomizeController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\ImportsOverviewController;
use App\Http\Controllers\ImportsResultsController;

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

// Main Route for our application, should serve the client side Vue application
Route::get('/', function () {
    $user = Auth::user() ? [
        'name' => Auth::user()->username
    ] : null;

    return inertia('Home', [
        'user' => $user
    ]);
})->name('home');

Route::get('/results', [ResultsController::class, 'index'])
    ->name('results');

Route::get('/random', [RandomizeController::class, 'index'])
    ->name('random');

Route::put('/mismatch-review', [ResultsController::class, 'update'])
    ->middleware('auth')
    ->name('mismatch-review');

// Mismatch store manager routes, might be converted to inertia routes in the future
Route::prefix('store')->name('store.')->group(function () {

    Route::redirect('/', '/store/api-settings')->name('home');

    Route::get('/api-settings', function () {
        return view('showToken', [
            'tokens' => Auth::user() ? Auth::user()->tokens : null,
            'upload_permission' => Gate::allows('upload-import')
        ]);
    })->name('api-settings');

    Route::get('/imports', function () {
        return view('importStatus', [
            'imports' => ImportMeta::with('error')->orderByDesc('id')->take(10)->get()
        ]);
    })->name('import-status');

    Route::get('/imports/{import_id}', [
        ImportsResultsController::class, 'showResultsCsv'
    ])->name('import-status/import_id');

    Route::get('/imports-overview', [
        ImportsOverviewController::class, 'downloadCsv'
    ])->name('imports-overview');
});

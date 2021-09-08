<?php

use App\Models\ImportMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

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

Route::get('/results', function (Request $request) {
    $user = Auth::user() ? [
        'name' => Auth::user()->username
    ] : null;

    $separator = config('mismatches.id_separator');
    $ids = explode($separator, $request->input('ids'));

    return inertia('Results', [
        'item_ids' => $ids,
        'user' => $user
    ]);
})->name('results');

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
});

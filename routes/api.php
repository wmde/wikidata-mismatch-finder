<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\MismatchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource(ImportController::RESOURCE_NAME, ImportController::class)
    ->only(['store', 'show', 'index']);

Route::apiResource(MismatchController::RESOURCE_NAME, MismatchController::class)
->only(['index']);

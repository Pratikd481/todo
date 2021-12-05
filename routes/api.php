<?php

use App\Http\Controllers\API\TaskController;
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

Route::post('/tasks/changeStatus', [TaskController::class, 'changeStatus'])->name('tasks.changeStatus');
Route::get('/tasks/pending', [TaskController::class, 'pendingList'])->name('tasks.pending');
Route::apiResource('tasks', TaskController::class);


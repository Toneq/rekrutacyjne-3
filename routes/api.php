<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorktimeController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/create-employeer', [EmployeeController::class, 'create']);
Route::post('/register-worktime', [WorktimeController::class, 'register']);
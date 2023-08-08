<?php

use App\Http\Controllers\StudentController;
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

Route::get('students', [StudentController::class, 'index']);
Route::get('test', [StudentController::class, 'test']);
// to store data
Route::post('student/store', [StudentController::class, 'store']);

// get student by id (no key, just value id)
Route::get('student/details/{id}', [StudentController::class, 'show']);

// to update data
Route::put('student/update/{id}', [StudentController::class, 'update']);

// to delete data
Route::delete('student/delete/{id}', [StudentController::class, 'destroy']);

// to search data by name or phone
Route::get('student/search/{name}', [StudentController::class, 'search']);

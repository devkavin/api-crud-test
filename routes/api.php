<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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

/**
 * Student Routes
 */
//TODO: Learn about Route shadows and how they word
Route::get('student/test/{id}', [StudentController::class, 'test']);
// Route::get('student/test/getname', [StudentController::class, 'test']);
// to get all data as paginated
Route::get('students/{page}/{limit}', [StudentController::class, 'index']);
// to store data
Route::post('student/store', [StudentController::class, 'store']);
// get student by id (no key, just value id)
Route::get('student/details/{id}', [StudentController::class, 'show']);
// to update data
// Route::put('student/update', [StudentController::class, 'update']);
// changed to post because form-data is used
Route::post('student/update', [StudentController::class, 'update']);
// to delete data
Route::delete('student/delete/{id}', [StudentController::class, 'delete']);
// to restore data
Route::put('student/restore/{id}', [StudentController::class, 'restore']);
// to force delete data
Route::delete('student/force-delete/{id}', [StudentController::class, 'forceDelete']);
// to search data by name or phone
Route::get('student/search/{name}', [StudentController::class, 'search']);
//
Route::resource('item', StudentController::class);

// image upload
Route::post('student/{id}/image', [StudentController::class, 'postImage']);
// image delete
Route::delete('student/{id}/image', [StudentController::class, 'deleteImage']);
// image view
Route::get('student/{id}/image', [StudentController::class, 'viewImage']);

/**
 * Teacher Routes
 */
Route::get('teacher/test', [TeacherController::class, 'test']);
// to get all data as paginated
Route::get('teachers/{page}/{limit}', [TeacherController::class, 'index']);
// to store data
Route::post('teacher/store', [TeacherController::class, 'store']);
// get teacher by id (no key, just value id)
Route::get('teacher/details/{id}', [TeacherController::class, 'show']);
// to update data
Route::post('teacher/update', [TeacherController::class, 'update']);
// to delete data
Route::delete('teacher/delete/{id}', [TeacherController::class, 'delete']);
// to restore data
Route::put('teacher/restore/{id}', [TeacherController::class, 'restore']);

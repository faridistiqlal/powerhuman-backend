<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::get('company', [CompanyController::class, 'fetch'])->name('fetch');
// Route::post('company', [CompanyController::class, 'create'])->middleware('auth:sanctum');
// Route::put('company', [CompanyController::class, 'update'])->middleware('auth:sanctum');

//SECTION - Company API
Route::prefix('company')->middleware('auth:sanctum')->name('comapny.')->group(function () {
    Route::get('', [CompanyController::class, 'fetch'])->name('fetch');
    Route::post('', [CompanyController::class, 'create'])->name('create');
    Route::post('update/{id}', [CompanyController::class, 'update'])->name('update');
});


//SECTION - Auth API
Route::name('auth.')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
        Route::get('user', [UserController::class, 'fetch'])->name('fetch');
    });
});

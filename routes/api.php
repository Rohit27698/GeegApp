<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Router;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::prefix('user')->group(function () {
        Route::post('/register', [\App\Http\Controllers\UserController::class, 'Register']);
        Route::post('/login', [\App\Http\Controllers\UserController::class, 'Login']);
        Route::get('/logout', [\App\Http\Controllers\UserController::class, 'Logout'])->middleware('auth:sanctum');
    });
    Route::prefix('creator')->group(function () {
        Route::post('/register', [\App\Http\Controllers\CreatorController::class, 'Register']);
        Route::post('/login', [\App\Http\Controllers\CreatorController::class, 'Login']);
        Route::get('/logout', [\App\Http\Controllers\CreatorController::class, 'Logout'])->middleware('auth:sanctum');
    });
});
Route::prefix('/geeg')->middleware('auth:sanctum')->group(function () {
    Route::post('/create', [\App\Http\Controllers\GeegController::class, 'Create']);
    Route::post('/update/{id}', [\App\Http\Controllers\GeegController::class, 'Update']);
    Route::delete('/delete/{id}', [\App\Http\Controllers\GeegController::class, 'Delete']);
    Route::get('/applications', [\App\Http\Controllers\GeegController::class, 'List']);
    Route::get('/apply/{id}', [\App\Http\Controllers\GeegController::class, 'Apply']);
    Route::get('/applications/{id}', [\App\Http\Controllers\GeegController::class, 'Applications']);
    Route::get('/myapplications', [\App\Http\Controllers\GeegController::class, 'MyApplications']);
    Route::get('/my-geegs', [\App\Http\Controllers\GeegController::class, 'MyGeegs']);
    Route::post("/assign-to/{id}", [\App\Http\Controllers\GeegController::class, 'AssignTo']);
});




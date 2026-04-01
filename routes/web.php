<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitGroupController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/locale', [LocaleController::class, 'update'])->name('locale.update');

    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [AuthController::class, 'user']);

    Route::resource('/api/users', UserController::class);
    Route::post('/api/users/{id}/updatePassword', [UserController::class, 'updatePassword']);

    Route::resource('/api/categories', CategoryController::class);
    Route::post('/api/categories/{category}/media', [CategoryController::class, 'storeMedia']);
    Route::delete('/api/categories/{category}/media', [CategoryController::class, 'destroyMedia']);

    Route::resource('/api/unit-groups', UnitGroupController::class);
    Route::resource('/api/units', UnitController::class);

    Route::get('/{any}', function () {
        return view('welcome');
    })->where('any', '.*');
});

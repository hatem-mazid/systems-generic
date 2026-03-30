<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
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
    Route::post('/api/user/{id}/updatePassword', [UserController::class, 'updatePassword']);

    Route::get('/{any}', function () {
        return view('welcome');
    })->where('any', '.*');
});

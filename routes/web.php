<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/{any}', function () {
        return view('welcome');
    })->where('any', '.*');
});

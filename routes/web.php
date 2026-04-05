<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitGroupController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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
    Route::post('/api/units/{id}/start-order', [UnitController::class, 'startOrder']);
    Route::post('/api/units/{id}/reserve', [UnitController::class, 'reserve']);
    Route::post('/api/units/{id}/close', [UnitController::class, 'close']);
    Route::post('/api/units/{id}/cancel-reservation', [UnitController::class, 'cancelReservation']);

    Route::post('/api/products/{product}/media', [ProductController::class, 'storeMedia']);
    Route::post('/api/products/{product}/media/default', [ProductController::class, 'setDefaultMedia']);
    Route::delete('/api/products/{product}/media', [ProductController::class, 'destroyMedia']);
    Route::resource('/api/products', ProductController::class);

    Route::resource('/api/orders', OrderController::class);
    Route::post('/api/orders/{order}/items', [OrderController::class, 'storeItem']);
    Route::delete('/api/orders/{order}/items/{item}', [OrderController::class, 'destroyItem']);

    Route::get('/{any}', function () {
        return view('welcome');
    })->where('any', '.*');
});

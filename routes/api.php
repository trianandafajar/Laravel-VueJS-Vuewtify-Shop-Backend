<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ShopController;
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

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::middleware(['auth:api'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // Category Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('random/{count}', [CategoryController::class, 'random']);
        Route::get('slug/{slug}', [CategoryController::class, 'slug']);
    });

    // Book Routes
    Route::prefix('books')->group(function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('top/{count}', [BookController::class, 'top']);
        Route::get('slug/{slug}', [BookController::class, 'slug']);
        Route::get('search/{keyword}', [BookController::class, 'search']);
        Route::post('cart', [BookController::class, 'cart']);
    });

    // Shop Routes
    Route::prefix('shop')->group(function () {
        Route::get('provinces', [ShopController::class, 'provinces']);
        Route::get('cities', [ShopController::class, 'cities']);
        Route::get('couriers', [ShopController::class, 'couriers']);
        
        Route::middleware(['auth:api'])->group(function () {
            Route::post('shipping', [ShopController::class, 'shipping']);
            Route::post('services', [ShopController::class, 'services']);
            Route::post('payment', [ShopController::class, 'payment']);
            Route::get('my-order', [ShopController::class, 'myOrder']);
        });
    });
});
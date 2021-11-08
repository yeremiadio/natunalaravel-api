<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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

Route::middleware(['api' => 'force-json'])->group(function () {
    //register new user
    Route::post('/register', [AuthenticationController::class, 'register']);
    //login user
    Route::post('/login', [AuthenticationController::class, 'login']);

    //Fetch Products
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{slug}', [ProductController::class, 'show']);
    });
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
    });

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}/update', [UserController::class, 'update']);
        });
        Route::get('/roles', [RoleController::class, 'index']);

        Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
            Route::group(['prefix' => 'category'], function () {
                Route::post('/create', [CategoryController::class, 'store']);
                Route::put('/{slug}/update', [CategoryController::class, 'update']);
                Route::delete('/{id}/delete', [CategoryController::class, 'destroy']);
            });
            Route::group(['prefix' => 'products'], function () {
                Route::post('/create', [ProductController::class, 'store']);
                Route::put('/{slug}/update', [ProductController::class, 'update']);
                Route::delete('/{id}/delete', [ProductController::class, 'destroy']);
            });
            Route::group(['prefix' => 'users'], function () {
                Route::post('/create', [UserController::class, 'store']);
                Route::delete('/{id}/delete', [UserController::class, 'destroy']);
            });
            Route::group(['prefix' => 'roles'], function () {
                Route::post('/create', [RoleController::class, 'store']);
                Route::put('/{id}/update', [RoleController::class, 'update']);
                Route::delete('/{id}/delete', [RoleController::class, 'destroy']);
            });
        });
    });

    //Logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});

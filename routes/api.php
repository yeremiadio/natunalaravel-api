<?php

use App\Http\Controllers\AuthenticationController;
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

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::get('/users', function (Request $request) {
            return $request->user();
        });
        Route::get('/roles', [RoleController::class, 'index']);

        Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
            Route::group(['prefix' => 'products'], function () {
                Route::post('/', [ProductController::class, 'index']);
                Route::post('/create', [ProductController::class, 'store']);
            });
            Route::group(['prefix' => 'users'], function () {
                Route::post('/create', [UserController::class, 'store']);
            });
            Route::group(['prefix' => 'roles'], function () {
                Route::post('/create', [RoleController::class, 'store']);
            });
        });
    });

    //Logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});

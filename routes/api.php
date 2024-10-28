<?php

use App\Http\Controllers\Dashboard\AboutController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/token', function () {
    $token = request()->user()->createToken('unifurniture');
    return response()->json([
        'token' => $token->plainTextToken
    ]);
});

//authentification olan user-ler daxil ola biler
Route::middleware('auth:sanctum')->group(function () {
    //Route::get('/user', [ProfileController::class, 'user']);

    //basket

    //DASHBOARD
    Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        //ABOUT PAGE
        Route::group(['prefix' => 'about', 'as' => 'about.'], function () {
            Route::get('/banner', [AboutController::class, 'getBanners']);
            Route::post('/banner', [AboutController::class, 'createBanner']);
            Route::post('/paralax', [AboutController::class, 'createParalax']);
        });
    });
});


//butun user-ler daxil ola biler
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/logout', [AuthController::class, 'logout']);

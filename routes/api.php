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
    Route::group(['prefix' => 'dashboard'], function () {
        //ABOUT PAGE
        Route::group(['prefix' => 'about'], function () {
            Route::group(['prefix' => 'banner'], function () {
                Route::get('/', [AboutController::class, 'getBanners']);
                Route::get('/{id}', [AboutController::class, 'getBanner']);
                Route::post('/create', [AboutController::class, 'createBanner']);
                Route::delete('/delete/{id}', [AboutController::class, 'deleteBanner']);
                Route::post('/edit/{id}', [AboutController::class, 'editBanner']);
                Route::post('/publish/{id}', [AboutController::class, 'publishBanner']);
            });
            Route::group(['prefix' => 'paralax'], function () {
                Route::get('/', [AboutController::class, 'getParalaxes']);
                Route::get('/{id}', [AboutController::class, 'getParalax']);
                Route::post('/create', [AboutController::class, 'createParalax']);
                Route::delete('/delete/{id}', [AboutController::class, 'deleteParalax']);
                Route::post('/edit/{id}', [AboutController::class, 'editParalax']);
                Route::post('/publish/{id}', [AboutController::class, 'publishParalax']);
            });
            Route::group(['prefix' => 'team-title'], function () {
                Route::get('/', [AboutController::class, 'getTeamTitles']);
                Route::get('/{id}', [AboutController::class, 'getTeamTitle']);
                Route::post('/create', [AboutController::class, 'createTeamTitle']);
                Route::delete('/delete/{id}', [AboutController::class, 'deleteTeamTitle']);
                Route::post('/edit/{id}', [AboutController::class, 'editTeamTitle']);
                Route::post('/publish/{id}', [AboutController::class, 'publishTitle']);
            });
            Route::group(['prefix' => 'team-member'], function () {
                Route::get('/', [AboutController::class, 'getTeamMembers']);
                Route::get('/{id}', [AboutController::class, 'getTeamMember']);
                Route::post('/create', [AboutController::class, 'createTeamMember']);
                Route::delete('/delete/{id}', [AboutController::class, 'deleteTeamMember']);
                Route::post('/edit/{id}', [AboutController::class, 'editTeamMember']);
            });
            Route::group(['prefix' => 'number-title'], function () {
                Route::get('/', [AboutController::class, 'getNumberTitles']);
                Route::get('/{id}', [AboutController::class, 'getNumberTitle']);
                Route::post('/create', [AboutController::class, 'createNumberTitle']);
                Route::delete('/delete/{id}', [AboutController::class, 'deleteNumberTitle']);
                Route::post('/edit/{id}', [AboutController::class, 'editNumberTitle']);
                Route::post('/publish/{id}', [AboutController::class, 'publishNumberTitle']);
            });
            Route::group(['prefix' => 'number-subtitle'], function () {
                Route::get('/', [AboutController::class, 'getNumberSubtitles']);
                Route::get('/{id}', [AboutController::class, 'getNumberSubtitle']);
                Route::post('/create', [AboutController::class, 'createNumberSubtitle']);
                Route::delete('/delete/{id}', [AboutController::class, 'deleteNumberSubtitle']);
                Route::post('/edit/{id}', [AboutController::class, 'editNumberSubtitle']);
            });
        });
    });
});


//butun user-ler daxil ola biler
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/logout', [AuthController::class, 'logout']);

//ABOUT PAGE
Route::get('/about/banner', [AboutController::class, 'publishedBanner']);
Route::get('/about/paralax', [AboutController::class, 'publishedParalax']);
Route::get('/about/team-title', [AboutController::class, 'publishedTeamTitle']);
Route::get('/about/team-member', [AboutController::class, 'getTeamMembers']);
Route::get('/about/number-title', [AboutController::class, 'publishedNumberTitle']);
Route::get('/about/number-subtitle', [AboutController::class, 'getNumberSubtitles']);

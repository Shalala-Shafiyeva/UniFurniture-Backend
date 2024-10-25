<?php

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

});


//butun user-ler daxil ola biler
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

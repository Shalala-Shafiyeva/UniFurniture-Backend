<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            //$token = $user->createToken('unifurniture');
            return response()->json([
                "data" => $user,
               // "token" => $token->plainTextToken,
                "message" => "User logged in successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "Invalid email or password",
                "success" => false
            ], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required:max:255',
            'surname' => 'required:max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:5',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }
        $user = new User();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        if ($user->save()) {
            return response()->json([
                "data" => $user,
                "message" => "User created successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "User not created",
                "success" => false
            ], 500);
        }
    }
}

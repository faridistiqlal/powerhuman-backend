<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            //NOTE Validate request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            //NOTE Find user by email
            $credential = request([
                'email',
                'password'
            ]);
            if (!Auth::attempt($credential)) {
                return ResponseFormatter::error(
                    'Unauthorized',
                    401
                );
            }
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid Credentials');
            }
            //NOTE Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            //NOTE Return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login Sukses');
        } catch (Exception $e) {
            return ResponseFormatter::error('Authentification Failed');
        }
    }
    public function register(Request $request)
    {
        try {
            //NOTE Validate request
            $request->validate([
                'name' => ['required', 'string',],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);
            //NOTE Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            //NOTE Generate Token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            //NOTE Return Response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Register Sukses');
        } catch (Exception $error) {
            //NOTE Return error resonse
            return ResponseFormatter::error([
                $error->getMessage()
            ]);
        }
    }
    public function logout(Request $request)
    {
        //NOTE Revoke Token

        $token = $request->user()->currentAccessToken()->delete();

        //NOTE Return Response  
        return ResponseFormatter::success($token, 'Logout Sukses');
    }
    public function fetch(Request $request)
    {

        //NOTE Get user
        $user = $request->user();
        return ResponseFormatter::success($user, 'Data Berhasil Ditemukan');
    }
}

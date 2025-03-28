<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // validate login details
        $validatedLogin = $request->validated();

        // Find user by email
        $user = User::where('email', $validatedLogin['email'])->first();

        // check password in request matches users password
        // if not a match then returns login failed
        if (! Hash::check($validatedLogin['password'], $user->password)) {
            return response()->json([
                'message' => 'login failed',
            ], 401);
        }

        // Generate token
        $token = $user->createToken('API Token')->plainTextToken;

        // Return token in the response
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'register_name' => 'required|string|min:2|max:255',
            'register_email' => 'required|string|email|max:255|unique:users,email',
            'register_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
            'register_password_confirmation' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $request['register_name'];
        $user->email = $request['register_email'];
        $user->password = Hash::make($request['register_password']);
        $user->save();

        return response()->json([
            'message' => 'Register successful',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}

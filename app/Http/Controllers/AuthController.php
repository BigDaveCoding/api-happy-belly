<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        if (!Hash::check($validatedLogin['password'], $user->password)) {
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
}

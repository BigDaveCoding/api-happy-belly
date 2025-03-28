<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // validate request data

        //

        return response()->json([
            'message' => 'login successful',
        ]);
    }
}

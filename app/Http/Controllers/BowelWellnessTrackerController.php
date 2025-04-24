<?php

namespace App\Http\Controllers;

use App\Models\BowelWellnessTracker;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BowelWellnessTrackerController extends Controller
{
    public function user(User $user): JsonResponse
    {

        $entries = BowelWellnessTracker::where(['user_id' => $user->id])->paginate(5);

        return response()->json([
            'message' => 'User entries found successfully',
            'data' => [
                'entries' => null,
                'pagination' => null,
            ]
        ]);
    }
}

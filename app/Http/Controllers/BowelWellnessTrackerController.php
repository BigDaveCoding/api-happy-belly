<?php

namespace App\Http\Controllers;

use App\Models\BowelWellnessTracker;
use App\Models\User;
use App\Providers\PaginationServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BowelWellnessTrackerController extends Controller
{
    public function user(User $user): JsonResponse
    {

        $entries = BowelWellnessTracker::where(['user_id' => $user->id])->paginate(5);
        $pagination = PaginationServiceProvider::pagination($entries);

        return response()->json([
            'message' => 'User entries found successfully',
            'data' => [
                'entries' => $entries->items(),
                'pagination' => $pagination,
            ]
        ]);
    }
}

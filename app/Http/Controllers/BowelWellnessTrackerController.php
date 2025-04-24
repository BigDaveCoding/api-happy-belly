<?php

namespace App\Http\Controllers;

use App\Models\BowelWellnessTracker;
use App\Models\User;
use App\Providers\PaginationServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BowelWellnessTrackerController extends Controller
{
    public function user(User $user, Request $request): JsonResponse
    {

        if (Auth::id() !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized - can only access your own entries'
            ], 401);
        }

        $request->validate([
            'pagination' => 'nullable|integer|min:1|max:50'
        ]);

        $entries = BowelWellnessTracker::query()->where(['user_id' => $user->id]);

        if($request->has('pagination')) {
            $entries = $entries->paginate($request->pagination);
        } else {
            $entries = $entries->paginate(5);
        }

        $pagination = PaginationServiceProvider::pagination($entries);

        $entry_data = $entries->getCollection()->transform(function ($entry) {
            return $entry->setHidden(['stool_type', 'urgency', 'pain', 'blood', 'blood_amount', 'stress_level', 'hydration_level', 'recent_meal', 'color', 'additional_notes', 'created_at', 'updated_at']);
        });

        return response()->json([
            'message' => 'User entries found successfully',
            'data' => [
                'entries' => $entry_data,
                'pagination' => $pagination,
            ]
        ]);
    }

    public function entry(BowelWellnessTracker $entry): JsonResponse
    {
        return response()->json([
            'message' => 'User single entry found successfully',
            'data' => $entry
        ]);
    }
}

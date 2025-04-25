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
                'message' => 'Unauthorized - can only access your own entries',
            ], 401);
        }

        $request->validate([
            'pagination' => 'nullable|integer|min:1|max:50',
        ]);

        $entries = BowelWellnessTracker::query()->where(['user_id' => $user->id]);

        if ($request->has('pagination')) {
            $entries = $entries->paginate($request->pagination);
        } else {
            $entries = $entries->paginate(5);
        }

        $pagination = PaginationServiceProvider::pagination($entries);

        $entry_data = $entries->getCollection()->transform(function ($entry) {
            return $entry->setHidden([
                'stool_type',
                'urgency',
                'pain',
                'blood',
                'blood_amount',
                'stress_level',
                'hydration_level',
                'recent_meal',
                'color',
                'additional_notes',
                'created_at',
                'updated_at',
            ]);
        });

        return response()->json([
            'message' => 'User entries found successfully',
            'data' => [
                'entries' => $entry_data,
                'pagination' => $pagination,
            ],
        ]);
    }

    public function entry(BowelWellnessTracker $entry): JsonResponse
    {
        if (Auth::id() !== $entry->user_id) {
            return response()->json([
                'message' => 'Unauthorized - can only access your own entries',
            ], 401);
        }

        return response()->json([
            'message' => 'User single entry found successfully',
            'data' => $entry,
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        // validate the data
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|string',
            'stool_type' => 'required|integer|min:1:max:7',
            'urgency' => 'nullable|integer|min:1|max:10',
            'pain' => 'nullable|integer|min:1|max:10',
            'blood' => 'nullable|boolean|min:1|max:10',
            'blood_amount' => 'nullable|integer|min:1|max:10000',
            'stress_level' => 'nullable|integer|min:1|max:10',
            'hydration_level' => 'nullable|integer|min:1|max:10',
            'recent_meal' => 'nullable|boolean',
            'color' => 'nullable|string',
            'additional_notes' => 'nullable|string|max:65535',
            'medications' => 'sometimes|array',
            'medications.*' => 'string|max:100'
        ]);

        // create new entry
        $entry = new BowelWellnessTracker();
        // save data to entry
        $entry->user_id = $request['user_id'];
        $entry->date = $request['date'];
        $entry->time = $request['time'];
        $entry->stool_type = $request['stool_type'];
        $entry->urgency  = $request['urgency'];
        $entry->pain = $request['pain'];
        $entry->blood = $request['blood'];
        $entry->blood_amount = $request['blood_amount'];
        $entry->stress_level = $request['stress_level'];
        $entry->hydration_level = $request['hydration_level'];
        $entry->recent_meal = $request['recent_meal'];
        $entry->additional_notes = $request['additional_notes'];
        // save the entry
        // Add pivot data

        return response()->json([
            'message' => 'Bowel Wellness Tracker entry created successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\BowelWellnessTrackerCreateRequest;
use App\Http\Requests\BowelWellnessTrackerUpdateMedicationPivotRequest;
use App\Http\Requests\BowelWellnessTrackerUpdateRequest;
use App\Models\BowelWellnessTracker;
use App\Models\User;
use App\Providers\BowelWellnessTrackerService;
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

    public function create(BowelWellnessTrackerCreateRequest $request): JsonResponse
    {
        if(Auth::id() !== $request['user_id']) {
            return response()->json([
                'message' => 'Unauthorized - can only create entries for yourself',
            ], 401);
        }
        $entry = BowelWellnessTrackerService::createEntry($request);
        BowelWellnessTrackerService::medicationPivotData($request, $entry);

        return response()->json([
            'message' => 'Bowel Wellness Tracker entry created successfully',
        ], 201);
    }

    public function update(BowelWellnessTracker $update, BowelWellnessTrackerUpdateRequest $request): JsonResponse
    {
        if (Auth::id() !== $update->user_id) {
            return response()->json([
                'message' => 'Unauthorized - can only update entries for yourself',
            ], 401);
        }
        $entry = BowelWellnessTracker::findOrFail($update->id);
        BowelWellnessTrackerService::updateBowelWellnessTrackerEntry($request, $entry);
        $entry->save();

        return response()->json([
            'message' => 'Bowel Wellness Tracker entry updated successfully',
        ]);
    }

    public function updateMedicationPivot(BowelWellnessTracker $updateMedication, BowelWellnessTrackerUpdateMedicationPivotRequest $request): JsonResponse
    {
        $updatedEntry = BowelWellnessTracker::findOrFail($updateMedication->id);

        if(Auth::id() !== $updatedEntry->user_id) {
            return response()->json([
                'message' => 'Unauthorized - can only update entries for yourself',
            ], 401);
        }

        $updatedEntry->medications()->detach();

        foreach($request['medication_id'] as $index => $medication_id){
            $updatedEntry->medications()->attach($medication_id, [
                'prescribed' => $request['medication_prescribed'][$index],
                'taken_at' => $request['medication_taken_at'][$index],
            ]);
        }

        return response()->json([
            'message' => 'Medication pivot updated successfully'
        ]);
    }
}

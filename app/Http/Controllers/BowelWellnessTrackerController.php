<?php

namespace App\Http\Controllers;

use App\Http\Requests\BowelWellnessTrackerCreateRequest;
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

    public function update(BowelWellnessTracker $update, Request $request): JsonResponse
    {

        $entry = BowelWellnessTracker::findOrFail($update->id);

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'date' => 'sometimes|date_format:Y-m-d',
            'time' => 'sometimes|date_format:H:i',
            'stool_type' => 'sometimes|integer|min:1|max:7',
            'urgency' => 'sometimes|nullable|integer|min:1|max:10',
            'pain' => 'sometimes|nullable|integer|min:1|max:10',
            'blood' => 'sometimes|nullable|boolean',
            'blood_amount' => 'sometimes|nullable|integer|min:1|max:10000',
            'stress_level' => 'sometimes|nullable|integer|min:1|max:10',
            'hydration_level' => 'sometimes|nullable|integer|min:1|max:10',
            'recent_meal' => 'sometimes|nullable|boolean',
            'color' => 'sometimes|nullable|string',
            'additional_notes' => 'sometimes|nullable|string|max:65535',
        ]);

        if(isset($request['date'])){
            $entry->date = $request['date'];
        }
        if(isset($request['time'])){
            $entry->time = $request['time'];
        }
        if(isset($request['stool_type'])){
            $entry->stool_type = $request['stool_type'];
        }
        if(isset($request['urgency'])){
            $entry->urgency = $request['urgency'];
        }
        if(isset($request['pain'])){
            $entry->pain = $request['pain'];
        }
        if(isset($request['blood'])){
            $entry->blood = $request['blood'];
        }
        if(isset($request['blood_amount'])){
            $entry->blood_amount = $request['blood_amount'];
        }
        if(isset($request['stress_level'])){
            $entry->stress_level = $request['stress_level'];
        }
        if(isset($request['hydration_level'])){
            $entry->hydration_level = $request['hydration_level'];
        }
        if(isset($request['recent_meal'])){
            $entry->recent_meal = $request['recent_meal'];
        }
        if(isset($request['color'])){
            $entry->color = $request['color'];
        }
        if(isset($request['additional_notes'])){
            $entry->additional_notes = $request['additional_notes'];
        }

        $entry->save();

        return response()->json([
            'message' => 'Bowel Wellness Tracker entry updated successfully',
        ]);
    }

    public function updateMedicationPivot(BowelWellnessTracker $updateMedication, Request $request): JsonResponse
    {

        $updatedEntry = BowelWellnessTracker::findOrFail($updateMedication->id);

        $request->validate([
            'medication_id' => 'required|array',
            'medication_id.*' => 'integer|exists:medications,id',
            'medication_prescribed' => 'sometimes|array',
            'medication_prescribed.*' => 'nullable|boolean|',
            'medication_taken_at' => 'sometimes|array',
            'medication_taken_at.*' => 'nullable|string',
        ]);

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

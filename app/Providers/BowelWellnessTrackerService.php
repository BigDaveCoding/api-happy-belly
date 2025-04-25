<?php

namespace App\Providers;

use App\Http\Requests\BowelWellnessTrackerCreateRequest;
use App\Models\BowelWellnessTracker;
use App\Models\Medication;
use Illuminate\Support\ServiceProvider;

class BowelWellnessTrackerService extends ServiceProvider
{
    public static function createEntry(BowelWellnessTrackerCreateRequest $request): BowelWellnessTracker
    {
        $entry = new BowelWellnessTracker;

        $entry->user_id = $request['user_id'];
        $entry->date = $request['date'];
        $entry->time = $request['time'];
        $entry->stool_type = $request['stool_type'];
        $entry->urgency = $request['urgency'];
        $entry->pain = $request['pain'];
        $entry->blood = $request['blood'];
        $entry->blood_amount = $request['blood_amount'];
        $entry->stress_level = $request['stress_level'];
        $entry->hydration_level = $request['hydration_level'];
        $entry->recent_meal = $request['recent_meal'];
        $entry->color = $request['color'];
        $entry->additional_notes = $request['additional_notes'];

        $entry->save();

        return $entry;
    }

    public static function medicationPivotData(BowelWellnessTrackerCreateRequest $request, BowelWellnessTracker $entry)
    {
        if (isset($request['medication_name'])) {
            foreach ($request['medication_name'] as $index => $medication) {
                $med = Medication::firstOrCreate([
                    'name' => $medication,
                    'strength' => $request['medication_strength'][$index],
                    'form' => $request['medication_form'][$index],
                    'route' => $request['medication_route'][$index],
                    'notes' => $request['medication_notes'][$index],
                ]);
                $entry->medications()->attach($med, [
                    'prescribed' => $request['medication_prescribed'][$index],
                    'taken_at' => $request['medication_taken_at'][$index],
                ]);
            }
        }
    }
}

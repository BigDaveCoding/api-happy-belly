<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BowelWellnessTrackerUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
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
        ];
    }
}

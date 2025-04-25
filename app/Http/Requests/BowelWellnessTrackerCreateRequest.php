<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BowelWellnessTrackerCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'stool_type' => 'required|integer|min:1|max:7',
            'urgency' => 'sometimes|nullable|integer|min:1|max:10',
            'pain' => 'sometimes|nullable|integer|min:1|max:10',
            'blood' => 'sometimes|nullable|boolean',
            'blood_amount' => 'sometimes|nullable|integer|min:1|max:10000',
            'stress_level' => 'sometimes|nullable|integer|min:1|max:10',
            'hydration_level' => 'sometimes|nullable|integer|min:1|max:10',
            'recent_meal' => 'sometimes|nullable|boolean',
            'color' => 'sometimes|nullable|string',
            'additional_notes' => 'sometimes|nullable|string|max:65535',
            'medication_name' => 'sometimes|array',
            'medication_name.*' => 'string|max:100',
            'medication_strength' => 'sometimes|array',
            'medication_strength.*' => 'nullable|string|max:100',
            'medication_form' => 'sometimes|array',
            'medication_form.*' => 'nullable|string|max:100',
            'medication_route' => 'sometimes|array',
            'medication_route.*' => 'nullable|string|max:100',
            'medication_notes' => 'sometimes|array',
            'medication_notes.*' => 'nullable|string|max:10000',
            'medication_prescribed' => 'sometimes|array',
            'medication_prescribed.*' => 'nullable|boolean|',
            'medication_taken_at' => 'sometimes|array',
            'medication_taken_at.*' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $names = $this->input('medication_name');
            $strengths = $this->input('medication_strength');
            $forms = $this->input('medication_form');
            $routes = $this->input('medication_route');

            if (is_array($names) && is_array($strengths) && is_array($forms) && is_array($routes)) {
                $array_counts = [count($names), count($strengths), count($forms), count($routes)];
                $unique_lengths = array_unique($array_counts);
                if (count($unique_lengths) > 1) {
                    $validator->errors()->add(
                        'bowel wellness tracker arrays',
                        'if medication provided - name, strength, form & route arrays must be of same length (can include null values)'
                    );
                }
            }
        });
    }
}

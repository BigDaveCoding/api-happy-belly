<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BowelWellnessTrackerUpdateMedicationPivotRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'medication_id' => 'required|array',
            'medication_id.*' => 'integer|exists:medications,id',
            'medication_prescribed' => 'sometimes|array',
            'medication_prescribed.*' => 'nullable|boolean|',
            'medication_taken_at' => 'sometimes|array',
            'medication_taken_at.*' => 'nullable|string',
        ];
    }
}

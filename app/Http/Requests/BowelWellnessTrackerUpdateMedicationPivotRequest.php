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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // creating arrays of passed in data
            $id = $this->medication_id;
            $prescribed = $this->medication_prescribed;
            $taken_at = $this->medication_taken_at;

            // if they're arrays
            if(is_array($id) && is_array($prescribed) && is_array($taken_at)){
                $lenghts = [count($id), count($prescribed), count($taken_at)];
                $unique_lengths = array_unique($lenghts);
                if (count($unique_lengths) > 1){
                    $validator->errors()->add('id, prescribed, taken_at', 'Length of each array must be the same (can include null for prescribed|taken_at)');
                }
           }
        });
    }
}

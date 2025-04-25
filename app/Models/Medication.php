<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    /** @use HasFactory<\Database\Factories\MedicationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'strength',
        'form',
        'route',
        'notes'
    ];

    public function bowelWellnessTrackers(): BelongsToMany
    {
        return $this->belongsToMany(BowelWellnessTracker::class)->withPivot('prescribed', 'taken_at');
    }
}

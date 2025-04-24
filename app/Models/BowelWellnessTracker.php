<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BowelWellnessTracker extends Model
{
    /** @use HasFactory<\Database\Factories\BowelWellnessTrackerFactory> */
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function medications(): BelongsToMany
    {
        return $this->BelongsToMany(Medication::class)->withPivot('prescribed', 'taken_at');
    }
}

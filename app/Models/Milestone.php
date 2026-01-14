<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    protected $fillable = [
        'habit_id',
        'streak_target',
        'icon',
        'title',
        'description',
        'achieved',
        'achieved_at',
        'order',
    ];

    protected $casts = [
        'achieved' => 'boolean',
        'achieved_at' => 'datetime',
    ];

    /**
     * Get the habit this milestone belongs to
     */
    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeatureFlag extends Model
{
    protected $table = 'user_feature_flags';

    protected $fillable = [
        'user_id',
        'feature_flag_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the feature flag
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class, 'feature_flag_id');
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

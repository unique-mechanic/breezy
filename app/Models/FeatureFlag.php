<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureFlag extends Model
{
    protected $fillable = [
        'name',
        'description',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get user-specific overrides for this feature
     */
    public function userOverrides(): HasMany
    {
        return $this->hasMany(UserFeatureFlag::class);
    }

    /**
     * Check if feature is enabled for a specific user
     */
    public function isEnabledForUser($userId): bool
    {
        $userOverride = $this->userOverrides()
            ->where('user_id', $userId)
            ->first();

        return $userOverride ? $userOverride->enabled : $this->enabled;
    }
}

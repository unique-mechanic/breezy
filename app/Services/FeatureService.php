<?php

namespace App\Services;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    const CACHE_PREFIX = 'feature_flag_';
    const CACHE_DURATION = 3600; // 1 hour

    /**
     * Check if a feature is enabled globally
     */
    public function isEnabled(string $featureName): bool
    {
        return $this->getFlag($featureName)?->enabled ?? false;
    }

    /**
     * Check if a feature is enabled for a specific user
     */
    public function isEnabledForUser(string $featureName, User $user): bool
    {
        $flag = $this->getFlag($featureName);
        
        if (!$flag) {
            return false;
        }

        return $flag->isEnabledForUser($user->id);
    }

    /**
     * Get a feature flag by name
     */
    public function getFlag(string $featureName): ?FeatureFlag
    {
        $cacheKey = self::CACHE_PREFIX . $featureName;
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($featureName) {
            return FeatureFlag::where('name', $featureName)->first();
        });
    }

    /**
     * Enable a feature globally
     */
    public function enable(string $featureName, string $description = null): FeatureFlag
    {
        $flag = FeatureFlag::firstOrCreate(
            ['name' => $featureName],
            ['enabled' => true, 'description' => $description]
        );

        if (!$flag->enabled) {
            $flag->update(['enabled' => true]);
        }

        $this->clearCache($featureName);
        return $flag;
    }

    /**
     * Disable a feature globally
     */
    public function disable(string $featureName): FeatureFlag
    {
        $flag = FeatureFlag::firstOrCreate(['name' => $featureName], ['enabled' => false]);
        
        if ($flag->enabled) {
            $flag->update(['enabled' => false]);
        }

        $this->clearCache($featureName);
        return $flag;
    }

    /**
     * Enable feature for a specific user
     */
    public function enableForUser(string $featureName, User $user): void
    {
        $flag = $this->getFlag($featureName) ?? $this->enable($featureName);
        
        $flag->userOverrides()->updateOrCreate(
            ['user_id' => $user->id],
            ['enabled' => true]
        );
    }

    /**
     * Disable feature for a specific user
     */
    public function disableForUser(string $featureName, User $user): void
    {
        $flag = $this->getFlag($featureName) ?? $this->enable($featureName);
        
        $flag->userOverrides()->updateOrCreate(
            ['user_id' => $user->id],
            ['enabled' => false]
        );
    }

    /**
     * Clear cache for a feature flag
     */
    public function clearCache(string $featureName): void
    {
        Cache::forget(self::CACHE_PREFIX . $featureName);
    }

    /**
     * Get all feature flags
     */
    public function getAllFlags()
    {
        return FeatureFlag::all();
    }
}

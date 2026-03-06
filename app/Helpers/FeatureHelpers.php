<?php

if (!function_exists('feature')) {
    /**
     * Check if a feature is enabled globally
     * 
     * @param string $featureName
     * @return bool
     */
    function feature(string $featureName): bool
    {
        return app('featureService')->isEnabled($featureName);
    }
}

if (!function_exists('userFeature')) {
    /**
     * Check if a feature is enabled for the authenticated user
     * 
     * @param string $featureName
     * @return bool
     */
    function userFeature(string $featureName): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return app('featureService')->isEnabledForUser($featureName, auth()->user());
    }
}

if (!function_exists('enableFeature')) {
    /**
     * Enable a feature globally
     * 
     * @param string $featureName
     * @param string|null $description
     * @return void
     */
    function enableFeature(string $featureName, string $description = null): void
    {
        app('featureService')->enable($featureName, $description);
    }
}

if (!function_exists('disableFeature')) {
    /**
     * Disable a feature globally
     * 
     * @param string $featureName
     * @return void
     */
    function disableFeature(string $featureName): void
    {
        app('featureService')->disable($featureName);
    }
}

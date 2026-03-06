<?php

namespace App\Http\Controllers;

use App\Models\FeatureFlag;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeatureFlagController extends Controller
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Show all feature flags
     */
    public function index(): Response
    {
        $flags = FeatureFlag::all()->map(function ($flag) {
            return [
                'id' => $flag->id,
                'name' => $flag->name,
                'description' => $flag->description,
                'enabled' => $flag->enabled,
                'user_count' => $flag->userOverrides()->count(),
            ];
        });

        return Inertia::render('FeatureFlags/Index', [
            'flags' => $flags,
        ]);
    }

    /**
     * Toggle a feature flag globally
     */
    public function toggle(Request $request, FeatureFlag $flag)
    {
        if ($flag->enabled) {
            $this->featureService->disable($flag->name);
        } else {
            $this->featureService->enable($flag->name, $flag->description);
        }

        return back()->with('success', "Feature '{$flag->name}' toggled successfully.");
    }

    /**
     * Toggle feature for authenticated user
     */
    public function toggleForUser(Request $request, string $featureName)
    {
        $user = auth()->user();
        
        if ($this->featureService->isEnabledForUser($featureName, $user)) {
            $this->featureService->disableForUser($featureName, $user);
            $enabled = false;
        } else {
            $this->featureService->enableForUser($featureName, $user);
            $enabled = true;
        }

        // Return JSON for API requests, redirect for form submissions
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Feature '{$featureName}' toggled for your account.",
                'enabled' => $enabled,
            ]);
        }

        return back()->with('success', "Feature '{$featureName}' toggled for your account.");
    }

    /**
     * Get feature flag status via API
     */
    public function status(string $featureName)
    {
        return response()->json([
            'feature' => $featureName,
            'enabled' => feature($featureName),
            'enabled_for_user' => userFeature($featureName),
        ]);
    }

    /**
     * Create a new feature flag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:feature_flags',
            'description' => 'nullable|string',
            'enabled' => 'required|boolean',
        ]);

        $flag = FeatureFlag::create($validated);

        return back()->with('success', 'Feature flag created successfully.');
    }
}

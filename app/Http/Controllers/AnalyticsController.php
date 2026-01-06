<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Services\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show user's overall analytics dashboard
     */
    public function dashboard(): Response
    {
        $user = auth()->user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return Inertia::render('Analytics/Dashboard', [
            'analytics' => $analytics,
        ]);
    }

    /**
     * Show analytics for a specific habit
     */
    public function habitAnalytics(Habit $habit): Response
    {
        $this->authorize('view', $habit);

        $analytics = $this->analyticsService->getHabitAnalytics($habit);

        return Inertia::render('Analytics/HabitAnalytics', [
            'analytics' => $analytics,
        ]);
    }
}

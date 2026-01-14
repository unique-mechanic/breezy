<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Services\MilestoneService;
use Inertia\Inertia;
use Inertia\Response;

class MilestoneController extends Controller
{
    protected MilestoneService $milestoneService;

    public function __construct(MilestoneService $milestoneService)
    {
        $this->milestoneService = $milestoneService;
    }

    /**
     * Show milestone progress for a specific habit
     */
    public function show(Habit $habit): Response
    {
        $this->authorize('view', $habit);

        // Check and update milestones
        $newlyAchieved = $this->milestoneService->checkAndUpdateMilestones($habit);

        // Get milestone progress
        $progress = $this->milestoneService->getMilestoneProgress($habit);

        return Inertia::render('Milestones/Show', [
            'habit' => [
                'id' => $habit->id,
                'name' => $habit->name,
                'color' => $habit->color,
            ],
            'progress' => $progress,
            'newly_achieved' => $newlyAchieved->map(fn($m) => [
                'icon' => $m->icon,
                'title' => $m->title,
                'description' => $m->description,
            ])->all(),
        ]);
    }

    /**
     * Show user's milestone dashboard
     */
    public function dashboard(): Response
    {
        $user = auth()->user();
        $stats = $this->milestoneService->getUserMilestoneStats($user);

        // Get habits with milestone data
        $habits = $user->habits()
            ->with('milestones')
            ->get()
            ->map(function ($habit) {
                $progress = $this->milestoneService->getMilestoneProgress($habit);
                return [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'color' => $habit->color,
                    'current_streak' => $habit->getCurrentStreak(),
                    'achieved_milestones' => count($progress['achieved']),
                    'total_milestones' => count(array_merge(
                        $progress['achieved'],
                        $progress['in_progress'],
                        $progress['upcoming']
                    )),
                ];
            });

        return Inertia::render('Milestones/Dashboard', [
            'stats' => $stats,
            'habits' => $habits,
        ]);
    }
}

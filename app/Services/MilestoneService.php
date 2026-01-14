<?php

namespace App\Services;

use App\Models\Habit;
use App\Models\Milestone;
use Carbon\Carbon;

class MilestoneService
{
    /**
     * Default milestones to create for a habit
     */
    const DEFAULT_MILESTONES = [
        ['streak' => 3, 'icon' => '🌱', 'title' => 'Seedling', 'description' => 'Keep it growing - 3 days done!'],
        ['streak' => 7, 'icon' => '🔥', 'title' => 'Week Wonder', 'description' => 'One week of consistency!'],
        ['streak' => 14, 'icon' => '💪', 'title' => 'Fortnight Fighter', 'description' => 'Two weeks strong!'],
        ['streak' => 30, 'icon' => '⭐', 'title' => 'Monthly Master', 'description' => 'A full month of dedication!'],
        ['streak' => 50, 'icon' => '🚀', 'title' => 'Rocket Launcher', 'description' => 'Halfway to a century!'],
        ['streak' => 100, 'icon' => '💯', 'title' => 'Century Champion', 'description' => '100 days of unstoppable momentum!'],
        ['streak' => 365, 'icon' => '👑', 'title' => 'Legendary Legend', 'description' => 'A full year of excellence!'],
    ];

    /**
     * Create default milestones for a habit
     */
    public function createDefaultMilestones(Habit $habit): void
    {
        foreach (self::DEFAULT_MILESTONES as $index => $data) {
            Milestone::firstOrCreate(
                [
                    'habit_id' => $habit->id,
                    'streak_target' => $data['streak'],
                ],
                [
                    'icon' => $data['icon'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'order' => $index,
                    'achieved' => false,
                ]
            );
        }
    }

    /**
     * Check and update milestones based on current streak
     */
    public function checkAndUpdateMilestones(Habit $habit): array
    {
        $currentStreak = $habit->getCurrentStreak();
        $newlyAchieved = [];

        $milestones = $habit->milestones()->get();

        foreach ($milestones as $milestone) {
            if (!$milestone->achieved && $currentStreak >= $milestone->streak_target) {
                $milestone->update([
                    'achieved' => true,
                    'achieved_at' => now(),
                ]);

                $newlyAchieved[] = $milestone;
            }
        }

        return $newlyAchieved;
    }

    /**
     * Get milestone progress for a habit
     */
    public function getMilestoneProgress(Habit $habit): array
    {
        $currentStreak = $habit->getCurrentStreak();
        $milestones = $habit->milestones()->get();

        $progress = [
            'current_streak' => $currentStreak,
            'achieved' => [],
            'in_progress' => [],
            'upcoming' => [],
        ];

        foreach ($milestones as $milestone) {
            $data = [
                'id' => $milestone->id,
                'icon' => $milestone->icon,
                'title' => $milestone->title,
                'description' => $milestone->description,
                'target' => $milestone->streak_target,
                'achieved_at' => $milestone->achieved_at,
            ];

            if ($milestone->achieved) {
                $progress['achieved'][] = $data;
            } elseif ($currentStreak >= $milestone->streak_target) {
                $progress['in_progress'][] = $data;
            } else {
                $daysRemaining = $milestone->streak_target - $currentStreak;
                $data['days_remaining'] = $daysRemaining;
                $data['percentage_complete'] = round(($currentStreak / $milestone->streak_target) * 100);
                $progress['upcoming'][] = $data;
            }
        }

        return $progress;
    }

    /**
     * Get all user milestones with stats
     */
    public function getUserMilestoneStats($user): array
    {
        $habits = $user->habits()->with('milestones')->get();

        $stats = [
            'total_milestones' => 0,
            'achieved_milestones' => 0,
            'in_progress_milestones' => 0,
            'achievement_rate' => 0,
        ];

        foreach ($habits as $habit) {
            foreach ($habit->milestones as $milestone) {
                $stats['total_milestones']++;
                if ($milestone->achieved) {
                    $stats['achieved_milestones']++;
                }
            }
        }

        if ($stats['total_milestones'] > 0) {
            $stats['achievement_rate'] = round(
                ($stats['achieved_milestones'] / $stats['total_milestones']) * 100,
                1
            );
        }

        return $stats;
    }
}

<?php

namespace App\Services;

use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Get analytics for a single habit
     */
    public function getHabitAnalytics(Habit $habit): array
    {
        return [
            'habit' => [
                'id' => $habit->id,
                'name' => $habit->name,
                'category' => $habit->category,
            ],
            'overview' => $this->getOverview($habit),
            'trends' => $this->getTrends($habit),
            'best_days' => $this->getBestDays($habit),
            'completion_by_week' => $this->getCompletionByWeek($habit),
            'completion_by_month' => $this->getCompletionByMonth($habit),
        ];
    }

    /**
     * Get overview statistics for a habit
     */
    private function getOverview(Habit $habit): array
    {
        $totalDays = 365;
        $totalCompletions = $habit->getTotalCompletions();
        $currentStreak = $habit->getCurrentStreak();
        $longestStreak = $habit->getLongestStreak();

        return [
            'total_completions' => $totalCompletions,
            'completion_percentage' => $habit->getCompletionPercentage(),
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'average_per_week' => round($totalCompletions / 52, 2),
            'average_per_month' => round($totalCompletions / 12, 2),
            'skipped_days' => $totalDays - $totalCompletions,
        ];
    }

    /**
     * Get completion trends over the past year (monthly)
     */
    private function getTrends(Habit $habit): array
    {
        $trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->clone()->startOfMonth();
            $monthEnd = $month->clone()->endOfMonth();

            $completions = $habit->logs()
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->count();

            $daysInMonth = $monthEnd->diffInDays($monthStart) + 1;

            $trends[] = [
                'month' => $month->format('M Y'),
                'month_short' => $month->format('M'),
                'completions' => $completions,
                'percentage' => round(($completions / $daysInMonth) * 100, 1),
            ];
        }

        return $trends;
    }

    /**
     * Get best days of the week for habit completion
     */
    private function getBestDays(Habit $habit): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dayData = [];

        for ($dayOfWeek = 1; $dayOfWeek <= 7; $dayOfWeek++) {
            $logs = $habit->logs()
                ->whereRaw('DAYOFWEEK(date) = ?', [$dayOfWeek])
                ->count();

            $dayData[] = [
                'day' => $days[$dayOfWeek - 1],
                'completions' => $logs,
            ];
        }

        usort($dayData, function ($a, $b) {
            return $b['completions'] <=> $a['completions'];
        });

        return $dayData;
    }

    /**
     * Get completion percentage by week
     */
    private function getCompletionByWeek(Habit $habit): array
    {
        $weeks = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->clone()->endOfWeek();

            $completions = $habit->logs()
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->count();

            $weeks[] = [
                'week' => 'Week ' . $weekStart->weekOfYear,
                'week_start' => $weekStart->format('M d'),
                'completions' => $completions,
                'percentage' => round(($completions / 7) * 100, 1),
            ];
        }

        return $weeks;
    }

    /**
     * Get completion percentage by month
     */
    private function getCompletionByMonth(Habit $habit): array
    {
        $months = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->clone()->startOfMonth();
            $monthEnd = $month->clone()->endOfMonth();

            $completions = $habit->logs()
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->count();

            $daysInMonth = $monthEnd->diffInDays($monthStart) + 1;

            $months[] = [
                'month' => $month->format('F Y'),
                'month_short' => $month->format('M'),
                'completions' => $completions,
                'days_in_month' => $daysInMonth,
                'percentage' => round(($completions / $daysInMonth) * 100, 1),
            ];
        }

        return $months;
    }

    /**
     * Get analytics for all user habits
     */
    public function getUserAnalytics($user): array
    {
        $habits = $user->habits()->with('logs')->get();

        return [
            'total_habits' => $habits->count(),
            'total_completions' => $habits->sum(fn($h) => $h->getTotalCompletions()),
            'average_completion_rate' => round(
                $habits->avg(fn($h) => $h->getCompletionPercentage()),
                1
            ),
            'habits_in_progress' => $habits->filter(fn($h) => $h->getCurrentStreak() > 0)->count(),
            'habits_by_category' => $this->getHabitsByCategory($habits),
            'total_streaks' => $habits->sum(fn($h) => $h->getCurrentStreak()),
        ];
    }

    /**
     * Get habits grouped by category with stats
     */
    private function getHabitsByCategory(Collection $habits): array
    {
        return $habits
            ->groupBy('category')
            ->map(fn($group) => [
                'count' => $group->count(),
                'total_completions' => $group->sum(fn($h) => $h->getTotalCompletions()),
                'average_completion_rate' => round(
                    $group->avg(fn($h) => $h->getCompletionPercentage()),
                    1
                ),
            ])
            ->toArray();
    }
}

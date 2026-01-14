<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Habit extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'frequency',
        'color',
        'goal_streak',
        'last_logged_date',
    ];

    protected $casts = [
        'last_logged_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('order');
    }

    /**
     * Get logs for the current year (365 days back)
     */
    public function getYearLogs()
    {
        $startDate = now()->subDays(365);
        
        return $this->logs()
            ->whereBetween('date', [$startDate, now()])
            ->orderBy('date')
            ->get()
            ->keyBy('date');
    }

    /**
     * Get current streak
     */
    public function getCurrentStreak(): int
    {
        $streak = 0;
        $currentDate = now();

        while (true) {
            $log = $this->logs()
                ->where('date', $currentDate->toDateString())
                ->first();

            if (!$log || !$log->completed) {
                break;
            }

            $streak++;
            $currentDate->subDay();
        }

        return $streak;
    }

    /**
     * Get longest streak
     */
    public function getLongestStreak(): int
    {
        $logs = $this->logs()
            ->orderBy('date')
            ->get();

        $longestStreak = 0;
        $currentStreak = 0;

        foreach ($logs as $log) {
            if ($log->completed) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }

        return $longestStreak;
    }

    /**
     * Get total completions
     */
    public function getTotalCompletions(): int
    {
        return $this->logs()->where('completed', true)->count();
    }

    /**
     * Get completion percentage for the year
     */
    public function getCompletionPercentage(): float
    {
        $totalDays = $this->logs()->count();
        
        if ($totalDays === 0) {
            return 0;
        }

        $completed = $this->getTotalCompletions();
        return round(($completed / $totalDays) * 100, 1);
    }

    /**
     * Log completion for today
     */
    public function logToday(bool $completed = true, ?string $notes = null): HabitLog
    {
        $today = now()->toDateString();

        $log = $this->logs()
            ->where('date', $today)
            ->first();

        if ($log) {
            $log->update([
                'completed' => $completed,
                'notes' => $notes,
            ]);
        } else {
            $log = $this->logs()->create([
                'date' => $today,
                'completed' => $completed,
                'notes' => $notes,
            ]);
        }

        $this->update(['last_logged_date' => $today]);

        return $log;
    }
}

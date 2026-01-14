<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Services\MilestoneService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HabitController extends Controller
{
    protected MilestoneService $milestoneService;

    public function __construct(MilestoneService $milestoneService)
    {
        $this->milestoneService = $milestoneService;
    }

    /**
     * Show the habits dashboard
     */
    public function index(): Response
    {
        $habits = auth()->user()->habits()
            ->with('logs')
            ->get()
            ->map(function ($habit) {
                return [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'description' => $habit->description,
                    'category' => $habit->category,
                    'frequency' => $habit->frequency,
                    'color' => $habit->color,
                    'current_streak' => $habit->getCurrentStreak(),
                    'longest_streak' => $habit->getLongestStreak(),
                    'total_completions' => $habit->getTotalCompletions(),
                    'completion_percentage' => $habit->getCompletionPercentage(),
                    'last_logged_date' => $habit->last_logged_date,
                    'year_logs' => $habit->getYearLogs(),
                ];
            });

        return Inertia::render('Habits/Index', [
            'habits' => $habits,
        ]);
    }

    /**
     * Show the form to create a new habit
     */
    public function create(): Response
    {
        return Inertia::render('Habits/Create', [
            'habit' => null,
        ]);
    }

    /**
     * Show a single habit with detailed view
     */
    public function show(Habit $habit): Response
    {
        $this->authorize('view', $habit);

        $logs = $habit->logs()
            ->orderByDesc('date')
            ->paginate(31);

        return Inertia::render('Habits/Show', [
            'habit' => [
                'id' => $habit->id,
                'name' => $habit->name,
                'description' => $habit->description,
                'category' => $habit->category,
                'frequency' => $habit->frequency,
                'color' => $habit->color,
                'current_streak' => $habit->getCurrentStreak(),
                'longest_streak' => $habit->getLongestStreak(),
                'total_completions' => $habit->getTotalCompletions(),
                'completion_percentage' => $habit->getCompletionPercentage(),
                'created_at' => $habit->created_at,
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * Show the form to edit a habit
     */
    public function edit(Habit $habit): Response
    {
        $this->authorize('update', $habit);

        return Inertia::render('Habits/Create', [
            'habit' => [
                'id' => $habit->id,
                'name' => $habit->name,
                'description' => $habit->description,
                'category' => $habit->category,
                'frequency' => $habit->frequency,
                'color' => $habit->color,
            ],
        ]);
    }

    /**
     * Store a new habit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly',
            'color' => 'required|string|regex:/^#[A-F0-9]{6}$/i',
        ]);

        $habit = auth()->user()->habits()->create($validated);

        // Create default milestones for this habit
        $this->milestoneService->createDefaultMilestones($habit);

        return redirect()->route('habits.show', $habit)
            ->with('success', 'Habit created successfully!');
    }

    /**
     * Update a habit
     */
    public function update(Request $request, Habit $habit)
    {
        $this->authorize('update', $habit);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly',
            'color' => 'required|string|regex:/^#[A-F0-9]{6}$/i',
        ]);

        $habit->update($validated);

        return redirect()->route('habits.show', $habit)
            ->with('success', 'Habit updated successfully!');
    }

    /**
     * Delete a habit
     */
    public function destroy(Habit $habit)
    {
        $this->authorize('delete', $habit);

        $habit->delete();

        return redirect()->route('habits.index')
            ->with('success', 'Habit deleted successfully!');
    }

    /**
     * Log a habit completion (API endpoint)
     */
    public function log(Request $request, Habit $habit)
    {
        $this->authorize('update', $habit);

        $validated = $request->validate([
            'date' => 'required|date',
            'completed' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $log = $habit->logs()
            ->updateOrCreate(
                ['date' => $validated['date']],
                [
                    'completed' => $validated['completed'],
                    'notes' => $validated['notes'] ?? null,
                ]
            );

        if ($validated['completed']) {
            $habit->update(['last_logged_date' => $validated['date']]);
        }

        return response()->json([
            'success' => true,
            'log' => $log,
            'current_streak' => $habit->getCurrentStreak(),
        ]);
    }

    /**
     * Toggle today's completion (quick log)
     */
    public function toggleToday(Habit $habit)
    {
        $this->authorize('update', $habit);

        $today = now()->toDateString();
        $log = $habit->logs()
            ->where('date', $today)
            ->first();

        $completed = $log ? !$log->completed : true;

        $log = $habit->logToday($completed);

        return response()->json([
            'success' => true,
            'completed' => $log->completed,
            'current_streak' => $habit->getCurrentStreak(),
        ]);
    }
}

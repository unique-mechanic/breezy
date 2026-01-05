# Habit Tracker Feature - Complete Documentation

Welcome! This guide explains how the habit tracker works, how it was built, and how to set it up. Even if you're new to Laravel, Eloquent, Vue, and PHP, this guide will walk you through everything step by step.

---

## Table of Contents

1. [What is This Feature?](#what-is-this-feature)
2. [How It Works (High Level)](#how-it-works-high-level)
3. [Project Structure](#project-structure)
4. [Database Layer](#database-layer)
5. [Backend Layer (Laravel)](#backend-layer-laravel)
6. [Frontend Layer (Vue)](#frontend-layer-vue)
7. [How Data Flows](#how-data-flows)
8. [Setup & Installation](#setup--installation)
9. [How to Use](#how-to-use)
10. [Key Concepts Explained](#key-concepts-explained)

---

## What is This Feature?

The Habit Tracker is a web application where users can:
- **Create habits** (e.g., "Exercise", "Read", "Meditate")
- **Log daily completions** by clicking on tiles
- **View a GitHub-style dashboard** with colored tiles showing the past year
- **Track streaks** (current and longest)
- **See statistics** (total completions, completion percentage)

### Example
If you create a "Morning Jog" habit, you'll see:
- A grid of colored tiles for the past 365 days
- Green tiles = completed days
- Gray tiles = skipped days
- Your current streak counter
- Your longest streak

---

## How It Works (High Level)

The app has 3 main layers:

```
┌─────────────────────────────────────────────────┐
│          FRONTEND (Vue.js)                       │
│  Dashboard, Forms, User Interface               │
└──────────────────┬──────────────────────────────┘
                   │ HTTP Requests/Responses
┌──────────────────▼──────────────────────────────┐
│          BACKEND (Laravel/PHP)                   │
│  Controllers, Models, Business Logic            │
└──────────────────┬──────────────────────────────┘
                   │ SQL Queries
┌──────────────────▼──────────────────────────────┐
│          DATABASE (SQLite/MySQL)                │
│  Habits table, Habit_logs table                 │
└─────────────────────────────────────────────────┘
```

**Flow Example:**
1. User clicks "Add Habit" button (Frontend)
2. Vue component sends form data to Laravel controller (HTTP POST)
3. Laravel validates the data and saves to database (SQL)
4. Database returns the created habit
5. Laravel sends back success response
6. Vue updates the page to show the new habit

---

## Project Structure

```
breezy/
├── app/
│   ├── Models/
│   │   ├── Habit.php           ← Model for habits
│   │   ├── HabitLog.php        ← Model for daily logs
│   │   └── User.php            ← Model for users (updated)
│   ├── Http/
│   │   └── Controllers/
│   │       ├── HabitController.php   ← Handles all habit logic
│   │       └── Controller.php        ← Base controller (updated)
│   └── Policies/
│       └── HabitPolicy.php     ← Authorization rules
├── database/
│   └── migrations/
│       ├── 2025_01_05_000003_create_habits_table.php
│       └── 2025_01_05_000004_create_habit_logs_table.php
├── resources/
│   └── js/
│       └── Pages/
│           └── Habits/
│               ├── Index.vue   ← Dashboard page
│               ├── Create.vue  ← Create/Edit form
│               └── Show.vue    ← Detail page
└── routes/
    └── web.php                ← URLs (updated)
```

---

## Database Layer

### What is a Database?

A database stores your application's data. Think of it like a spreadsheet with tables and rows.

### Migrations (Creating Tables)

Migrations are instructions that create or modify database tables. They're like version control for your database.

#### Habits Table Migration
**File:** `database/migrations/2025_01_05_000003_create_habits_table.php`

```php
Schema::create('habits', function (Blueprint $table) {
    $table->id();                          // Unique ID for each habit
    $table->foreignId('user_id')           // Which user owns this habit
        ->constrained()                    // Must reference a user
        ->cascadeOnDelete();               // Delete habit if user deleted
    $table->string('name');                // Habit name (max 255 chars)
    $table->string('description')          // Why you're building this habit
        ->nullable();                      // Can be empty
    $table->string('category');            // e.g., "health", "learning"
    $table->enum('frequency', ['daily', 'weekly']); // How often
    $table->string('color');               // Hex color #3B82F6
    $table->integer('goal_streak');        // Target streak
    $table->date('last_logged_date')       // Last time user logged
        ->nullable();
    $table->timestamps();                  // created_at, updated_at
});
```

**What does this do?**
- Creates a table called `habits`
- Defines columns (fields) for storing habit data
- `foreignId('user_id')` links habits to users
- `cascadeOnDelete()` means if you delete a user, their habits are also deleted

#### Habit Logs Table Migration
**File:** `database/migrations/2025_01_05_000004_create_habit_logs_table.php`

```php
Schema::create('habit_logs', function (Blueprint $table) {
    $table->id();                          // Unique ID
    $table->foreignId('habit_id')          // Which habit is this for
        ->constrained()
        ->cascadeOnDelete();
    $table->date('date');                  // What date is this log for?
    $table->boolean('completed');          // Did user complete it? true/false
    $table->text('notes')->nullable();     // Optional reflection/notes
    $table->timestamps();
    
    $table->unique(['habit_id', 'date']); // One log per habit per day
});
```

**What does this do?**
- Creates a table to track daily completions
- Each row = one day's log for one habit
- `unique(['habit_id', 'date'])` ensures you can't log the same habit twice on the same day

### Database Relationships

Think of relationships like real-world connections:

```
User (1) ──── (many) Habits
                         │
                         ├──── (many) Habit Logs

Example:
John's Account
├── Habit: Exercise
│   ├── Log: Jan 1 - Completed ✓
│   ├── Log: Jan 2 - Skipped ✗
│   └── Log: Jan 3 - Completed ✓
└── Habit: Read
    ├── Log: Jan 1 - Completed ✓
    └── Log: Jan 2 - Completed ✓
```

---

## Backend Layer (Laravel)

### What are Models?

Models are PHP classes that represent database tables. They handle:
- Getting data from the database
- Saving data to the database
- Business logic (like calculating streaks)

### Habit Model
**File:** `app/Models/Habit.php`

```php
class Habit extends Model
{
    // These fields can be mass-assigned (filled from user input)
    protected $fillable = [
        'user_id', 'name', 'description', 'category', 
        'frequency', 'color', 'goal_streak', 'last_logged_date'
    ];

    // Define relationships
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany {
        return $this->hasMany(HabitLog::class);
    }

    // Custom methods
    public function getCurrentStreak(): int { ... }
    public function getTotalCompletions(): int { ... }
    public function logToday(bool $completed = true): HabitLog { ... }
}
```

**Key Concepts:**

1. **Fillable**: Fields users can set when creating/editing
   ```php
   $habit = Habit::create([
       'name' => 'Exercise',      // OK - in fillable
       'color' => '#3B82F6',      // OK - in fillable
       'user_id' => 1             // OK - in fillable
   ]);
   ```

2. **Relationships**: How models connect to each other
   ```php
   // A habit belongs to one user
   $habit->user();              // Get the user who owns this habit
   
   // A habit has many logs
   $habit->logs()->get();       // Get all logs for this habit
   ```

3. **Helper Methods**: Custom functions for common tasks
   ```php
   $habit->getCurrentStreak();      // Get current streak (int)
   $habit->getTotalCompletions();   // Get total completed days (int)
   $habit->logToday(true);          // Mark today as complete
   ```

### HabitLog Model
**File:** `app/Models/HabitLog.php`

```php
class HabitLog extends Model
{
    protected $fillable = ['habit_id', 'date', 'completed', 'notes'];

    // A log belongs to one habit
    public function habit(): BelongsTo {
        return $this->belongsTo(Habit::class);
    }
}
```

This is simpler - it just stores the daily log data and links back to its habit.

### Controllers

Controllers handle requests from the frontend and return responses. They're like "actions" in your app.

**File:** `app/Http/Controllers/HabitController.php`

```php
class HabitController extends Controller
{
    // GET /habits → Show dashboard with all habits
    public function index(): Response { ... }

    // GET /habits/create → Show create form
    public function create(): Response { ... }

    // POST /habits → Save new habit to database
    public function store(Request $request) { ... }

    // GET /habits/{id} → Show habit detail page
    public function show(Habit $habit): Response { ... }

    // GET /habits/{id}/edit → Show edit form
    public function edit(Habit $habit): Response { ... }

    // PATCH /habits/{id} → Update habit
    public function update(Request $request, Habit $habit) { ... }

    // DELETE /habits/{id} → Delete habit
    public function destroy(Habit $habit) { ... }

    // POST /habits/{id}/log → API endpoint to log completion
    public function log(Request $request, Habit $habit) { ... }

    // POST /habits/{id}/toggle-today → Quick toggle for today
    public function toggleToday(Habit $habit) { ... }
}
```

**Understanding Each Method:**

1. **index()** - Dashboard
   ```php
   public function index(): Response
   {
       // Get all habits for logged-in user with their logs
       $habits = auth()->user()->habits()
           ->with('logs')
           ->get();

       // Calculate stats for each habit
       $habits = $habits->map(function ($habit) {
           return [
               'id' => $habit->id,
               'name' => $habit->name,
               'current_streak' => $habit->getCurrentStreak(),
               'longest_streak' => $habit->getLongestStreak(),
               'total_completions' => $habit->getTotalCompletions(),
               // ... more data
           ];
       });

       // Return Vue component with data
       return Inertia::render('Habits/Index', [
           'habits' => $habits,
       ]);
   }
   ```

2. **store()** - Create new habit
   ```php
   public function store(Request $request)
   {
       // Validate user input
       $validated = $request->validate([
           'name' => 'required|string|max:255',
           'category' => 'required|string|max:255',
           'frequency' => 'required|in:daily,weekly',
           'color' => 'required|string|regex:/^#[A-F0-9]{6}$/i',
       ]);

       // Create habit for logged-in user
       $habit = auth()->user()->habits()->create($validated);

       // Redirect back to detail page
       return redirect()->route('habits.show', $habit)
           ->with('success', 'Habit created!');
   }
   ```

3. **log()** - API endpoint to log completion
   ```php
   public function log(Request $request, Habit $habit)
   {
       // Check user owns this habit
       $this->authorize('update', $habit);

       // Validate the request
       $validated = $request->validate([
           'date' => 'required|date',
           'completed' => 'required|boolean',
           'notes' => 'nullable|string|max:1000',
       ]);

       // Create or update log for that date
       $log = $habit->logs()->updateOrCreate(
           ['date' => $validated['date']],
           [
               'completed' => $validated['completed'],
               'notes' => $validated['notes'] ?? null,
           ]
       );

       // Return JSON response (for frontend)
       return response()->json([
           'success' => true,
           'log' => $log,
           'current_streak' => $habit->getCurrentStreak(),
       ]);
   }
   ```

### Authorization (Policies)

Policies control what users can do.

**File:** `app/Policies/HabitPolicy.php`

```php
class HabitPolicy
{
    // Only habit owner can view
    public function view(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }

    // Only habit owner can update
    public function update(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }

    // Only habit owner can delete
    public function delete(User $user, Habit $habit): bool
    {
        return $user->id === $habit->user_id;
    }
}
```

**Usage in Controller:**
```php
public function update(Request $request, Habit $habit)
{
    // This checks: Can current user update this habit?
    $this->authorize('update', $habit);

    // If not, throws 403 error
    // If yes, continues...
}
```

### Routes

Routes map URLs to controller methods.

**File:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Only logged-in, verified users can access these

    Route::get('/habits', [HabitController::class, 'index'])
        ->name('habits.index');
    // GET /habits → Shows dashboard

    Route::get('/habits/create', [HabitController::class, 'create'])
        ->name('habits.create');
    // GET /habits/create → Shows create form

    Route::post('/habits', [HabitController::class, 'store'])
        ->name('habits.store');
    // POST /habits → Creates new habit

    Route::get('/habits/{habit}', [HabitController::class, 'show'])
        ->name('habits.show');
    // GET /habits/1 → Shows habit #1 detail

    Route::patch('/habits/{habit}', [HabitController::class, 'update'])
        ->name('habits.update');
    // PATCH /habits/1 → Updates habit #1

    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])
        ->name('habits.destroy');
    // DELETE /habits/1 → Deletes habit #1

    Route::post('/habits/{habit}/log', [HabitController::class, 'log'])
        ->name('habits.log');
    // POST /habits/1/log → Logs completion for habit #1
});
```

**Route Names:** The `->name()` allows you to reference routes in Vue:
```javascript
// In Vue:
<Link :href="`/habits/${habit.id}`">View</Link>
// Or using the name:
<Link :href="route('habits.show', habit.id)">View</Link>
```

---

## Frontend Layer (Vue)

Vue is a JavaScript framework that creates interactive user interfaces. It runs in the browser.

### Vue Basics

```vue
<template>
  <!-- HTML structure - what users see -->
  <div>
    <button @click="increment">Count: {{ count }}</button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// reactive data - when it changes, Vue updates the template
const count = ref(0);

// function
const increment = () => {
  count.value++;
};
</script>

<style scoped>
/* CSS for this component only */
button {
  background: blue;
}
</style>
```

**Key Concepts:**
- `{{ count }}` - Shows the value of `count` variable
- `@click="increment"` - Run `increment()` when clicked
- `ref(0)` - Create a reactive variable starting at 0
- `.value` - Access the actual value of a ref

### Habits Dashboard (Index.vue)

**File:** `resources/js/Pages/Habits/Index.vue`

This is the main dashboard showing all habits with GitHub-style tiles.

```vue
<template>
  <AuthenticatedLayout>
    <!-- Top header section -->
    <template #header>
      <h2>Habit Tracker</h2>
    </template>

    <div class="py-12">
      <!-- Add Habit Button -->
      <Link href="/habits/create">+ Add Habit</Link>

      <!-- No Habits Message -->
      <div v-if="habits.length === 0">
        No habits yet. Create your first one!
      </div>

      <!-- For Each Habit -->
      <div v-else>
        <div v-for="habit in habits" :key="habit.id">
          <!-- Habit Name & Stats -->
          <h3>{{ habit.name }}</h3>
          <div>
            <div>{{ habit.current_streak }} Current Streak</div>
            <div>{{ habit.longest_streak }} Longest Streak</div>
            <div>{{ habit.total_completions }} Completions</div>
            <div>{{ habit.completion_percentage }}% This Year</div>
          </div>

          <!-- GitHub-style Grid -->
          <div class="grid">
            <div v-for="(week, i) in getWeeks(habit.year_logs)">
              <!-- 7 days per week -->
              <button
                v-for="day in 7"
                :key="day"
                @click="toggleDay(habit.id, week, day)"
                :style="{ 
                  backgroundColor: getTileColor(habit, week, day, habit.color)
                }"
              >
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  habits: Array,  // Passed from Laravel controller
});

// Get 52 weeks (365 days) for the grid
const getWeeks = (yearLogs) => {
  // ... returns array of weeks
};

// Get the color for a tile based on completion
const getTileColor = (habit, week, dayIndex, baseColor) => {
  const date = new Date(week);
  date.setDate(date.getDate() + dayIndex - 1);
  const dateStr = date.toISOString().split('T')[0];

  const log = habit.year_logs[dateStr];

  if (!log) return '#ebedf0';              // Gray - no log
  if (log.completed) return baseColor;    // Green (or habit color)
  return adjustOpacity(baseColor, 0.2);   // Light green - attempted
};

// Toggle a specific day
const toggleDay = async (habitId, week, dayIndex) => {
  const date = new Date(week);
  date.setDate(date.getDate() + dayIndex - 1);
  const dateStr = date.toISOString().split('T')[0];

  // Send POST request to backend
  const response = await fetch(`/habits/${habitId}/log`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content,
    },
    body: JSON.stringify({
      date: dateStr,
      completed: true,
    }),
  });

  if (response.ok) {
    // Reload page to show updated data
    window.location.reload();
  }
};
</script>
```

**How the Grid Works:**

1. **getWeeks()** creates 52 weeks (365 days)
2. For each week, create 7 buttons (days)
3. Each button's color is determined by `getTileColor()`
4. When clicked, `toggleDay()` sends a POST request to the backend
5. Backend updates the database
6. Page reloads to show updated tiles

### Create/Edit Form (Create.vue)

**File:** `resources/js/Pages/Habits/Create.vue`

```vue
<template>
  <AuthenticatedLayout>
    <template #header>
      <h2>{{ habit ? 'Edit Habit' : 'Create New Habit' }}</h2>
    </template>

    <div>
      <form @submit.prevent="submit">
        <!-- Name Input -->
        <div>
          <label>Habit Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g., Exercise"
            required
          />
          <p v-if="form.errors.name">{{ form.errors.name }}</p>
        </div>

        <!-- Description -->
        <div>
          <label>Description</label>
          <textarea v-model="form.description"></textarea>
        </div>

        <!-- Category Select -->
        <div>
          <label>Category</label>
          <select v-model="form.category">
            <option value="health">Health & Fitness</option>
            <option value="learning">Learning</option>
            <!-- ... more options -->
          </select>
        </div>

        <!-- Color Picker -->
        <div>
          <label>Color</label>
          <input v-model="form.color" type="color" />
        </div>

        <!-- Submit Button -->
        <button type="submit" :disabled="form.processing">
          {{ habit ? 'Update' : 'Create' }} Habit
        </button>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  habit: Object,  // null for create, or habit object for edit
});

// Create a form object with Inertia
const form = useForm({
  name: props.habit?.name || '',
  description: props.habit?.description || '',
  category: props.habit?.category || 'general',
  frequency: props.habit?.frequency || 'daily',
  color: props.habit?.color || '#3B82F6',
});

// Submit form
const submit = () => {
  if (props.habit) {
    // Update existing habit
    form.patch(`/habits/${props.habit.id}`);
  } else {
    // Create new habit
    form.post('/habits');
  }
};
</script>
```

**Inertia.js Explanation:**

Inertia.js connects Laravel and Vue seamlessly. It:

1. **Makes HTTP Requests**: `form.post()`, `form.patch()`, `form.delete()`
2. **Handles Validation Errors**: `form.errors.name` shows validation messages
3. **Manages Loading State**: `form.processing` is `true` while request is pending

```javascript
// Simple POST request
form.post('/habits', {
  onSuccess: () => {
    // Redirect after success
    window.location.href = '/habits';
  }
});

// It automatically:
// - Sets Content-Type to application/json
// - Adds CSRF token
// - Handles errors
// - Updates form.errors object
```

### Detail Page (Show.vue)

Shows a single habit with recent activity.

```vue
<template>
  <AuthenticatedLayout>
    <div>
      <h2>{{ habit.name }}</h2>
      
      <!-- Stats -->
      <div>
        <div>{{ habit.current_streak }} Current Streak</div>
        <div>{{ habit.longest_streak }} Longest Streak</div>
        <div>{{ habit.total_completions }} Completions</div>
      </div>

      <!-- Recent Activity -->
      <div>
        <h3>Recent Activity</h3>
        <div v-for="log in logs.data" :key="log.id">
          <div>{{ log.date }}</div>
          <span>{{ log.completed ? '✓ Completed' : 'Skipped' }}</span>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
defineProps({
  habit: Object,  // Habit data from backend
  logs: Object,   // Paginated logs from backend
});
</script>
```

---

## How Data Flows

Let's trace a complete flow: **User creates a new habit**

### Step 1: User clicks "Create Habit"
```
User Interface (Browser)
  ↓
Vue renders /habits/create page
  ↓
Create.vue component displays form
```

### Step 2: User fills form and clicks "Create Habit"
```
User submits form
  ↓
Vue sends POST request to /habits
```

### Step 3: Backend receives request
```
Laravel receives POST /habits
  ↓
Routes.php says → HabitController@store
  ↓
HabitController.store() runs
  ↓
$request->validate() checks data
  ↓
auth()->user()->habits()->create($validated) creates habit in database
  ↓
Sends response with redirect
```

### Step 4: Database stores data
```
INSERT into habits table:
id: 1
user_id: 1
name: "Exercise"
category: "health"
color: "#3B82F6"
created_at: 2026-01-05 15:57:23
...
```

### Step 5: Frontend receives response
```
Vue receives redirect response
  ↓
Browser navigates to /habits/1
  ↓
HabitController.show() returns habit data
  ↓
Show.vue component renders with habit data
  ↓
User sees detail page
```

---

## Setup & Installation

### Prerequisites
You need:
- PHP 8.2+ (installed on your Mac)
- Laravel 11 (comes with Composer)
- Node.js & npm (for Vue)
- SQLite or MySQL (database)

### Step-by-Step Setup

#### 1. Clone/Download the Project
```bash
cd /Users/uma/code/breezy
```

#### 2. Install PHP Dependencies
```bash
composer install
```

This reads `composer.json` and installs Laravel and all packages.

#### 3. Create Environment File
```bash
cp .env.example .env
```

Edit `.env`:
```
DB_CONNECTION=sqlite        # Use SQLite
DB_DATABASE=/path/to/database.sqlite
```

#### 4. Generate Application Key
```bash
php artisan key:generate
```

This creates an encryption key for your app.

#### 5. Create Database File
```bash
touch database/database.sqlite
```

SQLite stores data in a file instead of needing a server.

#### 6. Run Migrations
```bash
php artisan migrate
```

This runs the migration files and creates:
- `users` table
- `habits` table
- `habit_logs` table

```
Running migrations...
✓ Created users table
✓ Created cache table
✓ Created jobs table
✓ Created habits table
✓ Created habit_logs table
```

#### 7. Install JavaScript Dependencies
```bash
npm install
```

This installs Vue, Inertia, and other frontend packages.

#### 8. Build Frontend Assets
```bash
npm run dev
```

This starts the Vite dev server that compiles Vue and CSS.

In another terminal:
```bash
php artisan serve
```

This starts Laravel server at `http://localhost:8000`

#### 9. Open in Browser
```
http://localhost:8000
```

Register a new account → Login → Go to /habits

---

## How to Use

### Create a Habit

1. Click **"Habits"** in navigation
2. Click **"+ Add Habit"** button
3. Fill form:
   - **Name**: What you want to track
   - **Description**: Why (optional)
   - **Category**: Pick one
   - **Frequency**: Daily or Weekly
   - **Color**: Pick a color
4. Click **"Create Habit"**

### Log a Completion

1. On habits dashboard, find your habit
2. See the grid of colored tiles
3. Click a tile to mark that day as complete
4. Tile changes from gray → green

### View Details

1. Click **"Edit"** on a habit card
2. See full stats and activity history
3. Make changes and click **"Update"**
4. Or click **"Delete"** to remove

---

## Key Concepts Explained

### Laravel Artisan (CLI)

Artisan is Laravel's command-line tool. Common commands:

```bash
# Database
php artisan migrate           # Run migrations
php artisan migrate:rollback  # Undo migrations

# Models
php artisan make:model Habit           # Create model
php artisan make:controller HabitController
php artisan make:migration create_habits_table

# Run server
php artisan serve             # Start dev server (localhost:8000)

# Clear cache
php artisan cache:clear
```

### Eloquent (ORM)

Eloquent is Laravel's database layer. It lets you query databases using PHP instead of SQL.

```php
// Instead of SQL: SELECT * FROM habits WHERE user_id = 1
$habits = Habit::where('user_id', 1)->get();

// Instead of: INSERT INTO habits (name, user_id) VALUES (...)
$habit = Habit::create(['name' => 'Exercise', 'user_id' => 1]);

// Instead of: UPDATE habits SET name = 'Gym' WHERE id = 1
$habit->update(['name' => 'Gym']);

// Instead of: DELETE FROM habits WHERE id = 1
$habit->delete();
```

### Blade Templates (Optional)

Blade is Laravel's templating language for HTML. Used in .blade.php files.

We use Inertia.js instead (which uses Vue), but Blade is good to know:

```blade
<!-- variables -->
<h1>{{ $title }}</h1>

<!-- if statements -->
@if($loggedIn)
  <p>Welcome!</p>
@endif

<!-- loops -->
@foreach($habits as $habit)
  <li>{{ $habit->name }}</li>
@endforeach

<!-- blade syntax is simpler than raw PHP/HTML -->
```

### Vite (Frontend Build Tool)

Vite compiles modern JavaScript/Vue and CSS into files browsers can read.

```bash
npm run dev        # Development mode (fast, includes source maps)
npm run build      # Production mode (minified, optimized)
```

Vite watches for changes and automatically rebuilds.

### Inertia.js (Vue ↔ Laravel Bridge)

Inertia connects Vue and Laravel without needing a separate API.

**Traditional SPA (Single Page App):**
```
Browser → /api/habits → JSON → Process in Vue
```

**Inertia:**
```
Browser → /habits → Return rendered Vue page with data
```

**Benefits:**
- Simpler than traditional APIs
- Type-safe (with TypeScript)
- No separate API documentation needed
- Session/auth automatically work

### Environment Variables (.env)

`.env` file stores sensitive config that shouldn't be in code:

```
APP_NAME=Breezy
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:xxx...

DB_CONNECTION=sqlite
DB_DATABASE=/path/database.sqlite

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
```

Never commit `.env` to Git. Use `.env.example` as template.

### Middleware

Middleware are "gates" that requests pass through.

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Only authenticated, verified users can access
    Route::get('/habits', ...);
});
```

**Common middleware:**
- `auth` - User must be logged in
- `verified` - User email must be verified
- `admin` - User must be admin
- `throttle` - Rate limiting

---

## Troubleshooting

### "Module not found" Error
```bash
npm install
npm run dev
```

### "Connection refused" when visiting site
```bash
php artisan serve
```

Start Laravel server in another terminal.

### Database errors
```bash
php artisan migrate:fresh     # Recreate all tables
php artisan migrate           # Run migrations
```

### Permission denied errors
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### "CSRF token mismatch"
Make sure you're including the CSRF token in forms:
```html
<input type="hidden" name="csrf-token" :content="csrfToken">
```

---

## Next Steps & Learning

### Improve the Feature
- Add habit reminders/notifications
- Export data to CSV
- Add tags/filtering
- Social sharing features
- Mobile app version

### Learn More
- **Laravel Docs**: https://laravel.com/docs
- **Vue Docs**: https://vuejs.org/guide
- **Eloquent**: https://laravel.com/docs/eloquent
- **Inertia.js**: https://inertiajs.com

### Practice Exercises
1. Add a "Notes" field to log completions
2. Create a "Weekly Summary" page
3. Add habit categories with filtering
4. Create a "Friends" feature to compare streaks
5. Add habit templates for quick creation

---

## Summary

The habit tracker demonstrates:
- **Database Design**: Tables, migrations, relationships
- **Backend Logic**: Models, controllers, authorization
- **Frontend UI**: Vue components, forms, interactive grids
- **Full-Stack Integration**: How Laravel and Vue work together

Everything is built with best practices in mind and should serve as a good reference for your future Laravel projects!

Happy tracking! 🎉

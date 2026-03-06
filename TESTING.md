# Testing Guide - Breezy Habit Tracker

Complete guide to testing the Breezy habit tracker application.

## Table of Contents

1. [Setup (Prerequisites)](#setup-prerequisites)
2. [Database Setup](#database-setup)
3. [Automated Testing](#automated-testing)
4. [Start the Development Server](#start-the-development-server)
5. [Manual Testing Workflow](#manual-testing-workflow)
6. [Key Test Files](#key-test-files)
7. [Database/API Testing with Laravel Tinker](#databaseapi-testing-with-laravel-tinker)
8. [Common Issues & Troubleshooting](#common-issues--troubleshooting)

---

## Setup (Prerequisites)

First, make sure dependencies are installed:

```bash
cd /Users/uma/code/breezy

# Install PHP dependencies (if not already done)
composer install

# Install Node dependencies (if not already done)
npm install

# Create .env file (if it doesn't exist)
cp .env.example .env

# Generate app key
php artisan key:generate
```

---

## Database Setup

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations to create tables
php artisan migrate
```

---

## Automated Testing

The project uses **Pest** (modern PHP testing framework):

```bash
# Run all tests
php artisan test

# Run only Feature tests
php artisan test tests/Feature

# Run only Unit tests
php artisan test tests/Unit

# Run with verbose output to see details
php artisan test --verbose

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

---

## Start the Development Server

Run both backend & frontend servers simultaneously:

### **All-in-One Command (Recommended)**

```bash
composer run dev
```

This runs:
- Laravel server (port 8000)
- Vite dev server for frontend hotreload
- Queue listener
- Logs stream

### **Or Manually in Separate Terminals**

**Terminal 1: PHP server**
```bash
php artisan serve
```

**Terminal 2: Frontend bundler**
```bash
npm run dev
```

Then visit: **http://localhost:8000**

---

## Manual Testing Workflow

### **A. Basic Setup**

1. Create an account (register page)
2. Log in
3. Go to → Dashboard

### **B. Test Habit Creation**

1. Click "Create Habit"
2. Fill in:
   - **Name:** "Morning Jog"
   - **Category:** "Health"
   - **Frequency:** "Daily"
   - **Color:** Pick a color
3. Submit → Should redirect to habit detail page

### **C. Test Habit Logging**

1. Go to Habits Dashboard
2. Click on a habit card
3. See the year grid (365 days)
4. Click tiles to log completion for specific days
5. Check the streak counters update

### **D. Test Analytics**

1. Navigate to `/analytics`
2. See completion %, trends, best days
3. Click on a specific habit for detailed analytics

### **E. Test Edit/Delete**

1. On habit detail page, click "Edit"
2. Change habit name
3. Submit → Should update
4. Click "Delete" → Should remove from dashboard

---

## Key Test Files

- `tests/Feature/ExampleTest.php` - Example feature tests
- `tests/Feature/ProfileTest.php` - Profile functionality
- `phpunit.xml` - Test configuration (SQLite in-memory DB, no real data affected)

### Test Configuration

The `phpunit.xml` file configures:
- **Database:** SQLite in-memory (`:memory:`) - safe, isolated testing
- **Environment:** Testing mode with mocked services
- **Test Suites:** Unit & Feature tests organized separately

---

## Database/API Testing with Laravel Tinker

Quick REPL to test backend logic:

```bash
php artisan tinker
```

### Common Tinker Commands

```php
# Create a test user
$user = User::factory()->create()

# Create a habit for that user
$habit = $user->habits()->create([
    'name' => 'Test Habit',
    'category' => 'Test',
    'frequency' => 'daily',
    'color' => '#3B82F6'
])

# Test streak calculation
$habit->getCurrentStreak()

# Check logs
$habit->logs()->count()

# Get all logs for a habit
$habit->logs()->get()

# Get completion percentage
$habit->getCompletionPercentage()

# Get total completions
$habit->getTotalCompletions()
```

---

## Common Issues & Troubleshooting

| Issue | Solution |
|-------|----------|
| `php artisan serve` fails | Check PHP is installed: `php --version` |
| Port 8000 in use | Change port: `php artisan serve --port=8001` |
| Database locked | Delete `database/database.sqlite` and run migrations again |
| Tests fail | Run `php artisan migrate:refresh --seed` before tests |
| Frontend not loading | Make sure `npm run dev` is running in another terminal |
| NPM errors | Delete `node_modules` and `npm install` again |
| Composer errors | Delete `vendor` and `composer install` again |

---

## Development Workflow

### **Quick Start**

```bash
# One command to start everything
composer run dev
```

### **Testing + Development**

In one terminal:
```bash
composer run dev
```

In another terminal:
```bash
# Run tests while developing
php artisan test --watch
```

### **Database Reset**

```bash
# Reset and migrate fresh
php artisan migrate:refresh

# Reset with seeders
php artisan migrate:refresh --seed
```

---

## Next Steps

- Read [HABIT_TRACKER_GUIDE.md](HABIT_TRACKER_GUIDE.md) for architecture details
- Check [routes/web.php](routes/web.php) for all available endpoints
- Explore [app/Http/Controllers/HabitController.php](app/Http/Controllers/HabitController.php) for business logic

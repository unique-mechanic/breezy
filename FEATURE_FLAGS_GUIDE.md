# Feature Flags Implementation Guide

Complete guide to using the feature flag system for managing features in the Breezy habit tracker.

---

## Overview

Feature flags allow you to:
- **Enable/disable features** without redeploying
- **Gradual rollout** to users
- **A/B testing** different implementations
- **Easy rollback** if something breaks
- **Per-user feature access** for beta testing

---

## Architecture

```
FeatureFlag (global flag)
  ├── name: "dark_mode" (unique)
  ├── description: "Dark mode theme"
  ├── enabled: true/false (global toggle)
  └── UserFeatureFlag[] (per-user overrides)
      ├── user_id
      └── enabled
```

**Logic:**
1. User has feature enabled if they have a USER override → use override
2. Otherwise → use GLOBAL flag setting

This allows:
- Global default (all users)
- User exceptions (some users get feature, others don't)

---

## Database Tables

### `feature_flags` table
```
id | name | description | enabled | created_at | updated_at
```

### `user_feature_flags` table
```
id | user_id | feature_flag_id | enabled | created_at | updated_at
```

---

## Using Feature Flags

### In PHP (Controllers, Services, etc.)

#### Check global feature
```php
use App\Services\FeatureService;

// In controller or service
$featureService = app('featureService');

if ($featureService->isEnabled('dark_mode')) {
    // Feature is enabled globally
}
```

#### Check for current user
```php
use App\Services\FeatureService;

$featureService = app('featureService');

if ($featureService->isEnabledForUser('dark_mode', auth()->user())) {
    // Feature is enabled for this user
}
```

### Using Helper Functions

```php
// Check global flag
if (feature('dark_mode')) {
    // Feature is enabled
}

// Check for authenticated user
if (userFeature('dark_mode')) {
    // Feature is enabled for current user
}

// Enable/disable features
enableFeature('dark_mode', 'Toggle dark theme');
disableFeature('dark_mode');
```

### In Blade Templates
```blade
@if(userFeature('dark_mode'))
    <body class="dark-mode">
        <!-- Dark mode content -->
    </body>
@else
    <body class="light-mode">
        <!-- Light mode content -->
    </body>
@endif
```

### In Vue Components

#### Get feature status via API
```javascript
// In a Vue component
async function checkFeatureStatus() {
    const response = await axios.get('/api/features/dark_mode');
    const { enabled, enabled_for_user } = response.data;
    
    if (enabled_for_user) {
        console.log('Dark mode is enabled for this user');
    }
}
```

#### Let user toggle feature
```javascript
async function toggleDarkMode() {
    const response = await axios.post('/features/dark_mode/toggle-user');
    console.log('Dark mode toggled');
}
```

---

## Management (Admin)

### Command Line

#### Enable feature globally
```bash
php artisan tinker

# Get feature service
$service = app('featureService');

# Enable feature
$service->enable('dark_mode', 'Dark theme for application');

# Or disable
$service->disable('dark_mode');
```

#### Enable for specific user
```bash
php artisan tinker

$service = app('featureService');
$user = User::find(1);

# Enable for user
$service->enableForUser('dark_mode', $user);

# Disable for user
$service->disableForUser('dark_mode', $user);
```

### Web Admin Panel

Visit `/admin/features` (requires authentication) to manage feature flags:
- View all features
- Toggle global flags
- See how many users have per-user overrides

---

## Adding New Feature Flags

### 1. Add to seeder (database/seeders/FeatureFlagSeeder.php)

```php
$features = [
    [
        'name' => 'my_new_feature',
        'description' => 'What this feature does',
        'enabled' => false,  // Start disabled
    ],
];
```

### 2. Run seeder
```bash
php artisan db:seed --class=FeatureFlagSeeder
```

### 3. Use in code
```php
if (feature('my_new_feature')) {
    // Your new feature code
}
```

---

## Built-in Features

### 1. `dark_mode`
**Description:** Dark mode theme toggle
**Status:** Disabled by default
**Per-user:** Yes (users can toggle for themselves)

### 2. `social_sharing`
**Description:** Share habits on social media
**Status:** Disabled by default
**Per-user:** No (global only)

### 3. `notifications`
**Description:** Send habit reminders
**Status:** Disabled by default
**Per-user:** Yes (opt-in)

### 4. `habit_templates`
**Description:** Pre-made habit categories
**Status:** Disabled by default
**Per-user:** No

### 5. `advanced_analytics`
**Description:** Advanced charts and insights
**Status:** Disabled by default
**Per-user:** No

---

## Implementation Example: Dark Mode

### Backend Setup

#### In your layout/component controller:
```php
class HabitController extends Controller
{
    public function index()
    {
        return Inertia::render('Habits/Index', [
            'darkModeEnabled' => userFeature('dark_mode'),
        ]);
    }
}
```

### Frontend Setup

#### In Vue Component:
```vue
<template>
  <div :class="darkModeEnabled ? 'dark' : 'light'">
    <!-- Your content -->
    
    <div class="settings">
      <button @click="toggleDarkMode">
        {{ darkModeEnabled ? 'Light Mode' : 'Dark Mode' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

defineProps({
  darkModeEnabled: Boolean,
})

const isDarkMode = ref(false)

onMounted(async () => {
  const response = await axios.get('/api/features/dark_mode')
  isDarkMode.value = response.data.enabled_for_user
})

async function toggleDarkMode() {
  await axios.post('/features/dark_mode/toggle-user')
  isDarkMode.value = !isDarkMode.value
}
</script>

<style scoped>
.dark {
  background: #1f2937;
  color: #f3f4f6;
}

.light {
  background: white;
  color: #1f2937;
}
</style>
```

---

## Testing Feature Flags

### Unit Test Example
```php
// tests/Feature/FeatureFlagsTest.php
test('dark mode feature can be toggled', function () {
    $featureService = app('featureService');
    
    // Should be disabled by default
    expect($featureService->isEnabled('dark_mode'))->toBeFalse();
    
    // Enable it
    $featureService->enable('dark_mode');
    expect($featureService->isEnabled('dark_mode'))->toBeTrue();
});
```

### Feature Test Example
```php
test('authenticated user can toggle dark mode', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/features/dark_mode/toggle-user');
    
    expect($response->status())->toBe(302);
    expect(userFeature('dark_mode'))->toBeTrue();
});
```

---

## Caching

Feature flags are cached for **1 hour** to improve performance:
- Checking a flag queries cache first
- Enabling/disabling a flag clears the cache
- Cache key: `feature_flag_{featureName}`

### Manual cache clear
```bash
php artisan tinker

$service = app('featureService');
$service->clearCache('dark_mode');
```

---

## Best Practices

✅ **DO:**
- Start with features disabled
- Enable gradually for testing
- Use per-user flags for beta releases
- Document what each flag does
- Clear cache after database changes
- Name flags descriptively (`dark_mode`, not `dm`)

❌ **DON'T:**
- Leave old flags in code after feature is stable
- Enable features globally without testing
- Hard-code feature names (use variables)
- Forget to update seeder when adding new flags

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Flag doesn't work | Clear cache: `$service->clearCache('feature_name')` |
| Cache stale | Restart server or manually clear cache |
| Feature not found | Check seeder, run `php artisan db:seed --class=FeatureFlagSeeder` |
| Helper function not found | Run `composer dump-autoload` |

---

## Next Steps

1. **Dark Mode Implementation** - Follow example above
2. **Test feature flags** - Use test examples
3. **Deploy to production** - Enable gradually
4. **Monitor usage** - Check admin panel for user counts

<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'name' => 'dark_mode',
                'description' => 'Dark mode theme for the application',
                'enabled' => true,
            ],
            [
                'name' => 'social_sharing',
                'description' => 'Allow users to share their habits on social media',
                'enabled' => false,
            ],
            [
                'name' => 'notifications',
                'description' => 'Send reminders and notifications to users',
                'enabled' => false,
            ],
            [
                'name' => 'habit_templates',
                'description' => 'Pre-made habit templates for quick setup',
                'enabled' => false,
            ],
            [
                'name' => 'advanced_analytics',
                'description' => 'Advanced analytics and insights for habits',
                'enabled' => false,
            ],
        ];

        foreach ($features as $feature) {
            FeatureFlag::firstOrCreate(
                ['name' => $feature['name']],
                $feature
            );
        }
    }
}

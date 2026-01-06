<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Habits -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Total Habits</p>
                                <p class="text-3xl font-bold text-gray-900">{{ analytics.total_habits }}</p>
                            </div>
                            <div class="text-4xl text-blue-500 opacity-20">📋</div>
                        </div>
                    </div>

                    <!-- Total Completions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Total Completions</p>
                                <p class="text-3xl font-bold text-gray-900">{{ analytics.total_completions }}</p>
                            </div>
                            <div class="text-4xl text-green-500 opacity-20">✓</div>
                        </div>
                    </div>

                    <!-- Average Completion Rate -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Avg Completion Rate</p>
                                <p class="text-3xl font-bold text-gray-900">{{ analytics.average_completion_rate }}%</p>
                            </div>
                            <div class="text-4xl text-purple-500 opacity-20">📊</div>
                        </div>
                    </div>

                    <!-- Habits In Progress -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Active Streaks</p>
                                <p class="text-3xl font-bold text-gray-900">{{ analytics.habits_in_progress }}</p>
                            </div>
                            <div class="text-4xl text-orange-500 opacity-20">🔥</div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Habits by Category</h3>
                    
                    <div v-if="Object.keys(analytics.habits_by_category).length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="(stats, category) in analytics.habits_by_category" 
                            :key="category"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <p class="text-sm font-medium text-gray-600 capitalize">{{ category }}</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.count }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ stats.total_completions }} completions</p>
                            <p class="text-xs text-gray-500">{{ stats.average_completion_rate }}% avg rate</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-sm">No habits created yet.</p>
                </div>

                <!-- Total Streaks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">📈 Total Combined Streaks</h3>
                    <div class="flex items-baseline">
                        <p class="text-5xl font-bold text-orange-500">{{ analytics.total_streaks }}</p>
                        <p class="text-gray-600 ml-3">days across all active habits</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    analytics: {
        type: Object,
        required: true,
    },
});
</script>

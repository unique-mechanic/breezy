<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">{{ analytics.habit.name }} - Analytics</h2>
                <Link :href="route('habits.show', analytics.habit.id)" class="text-blue-600 hover:text-blue-900">
                    Back to Habit
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 font-medium">Completion %</p>
                        <p class="text-3xl font-bold text-green-600">{{ analytics.overview.completion_percentage }}%</p>
                        <p class="text-xs text-gray-500 mt-2">{{ analytics.overview.total_completions }} of {{ 365 }} days</p>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 font-medium">Current Streak</p>
                        <p class="text-3xl font-bold text-orange-600">{{ analytics.overview.current_streak }}</p>
                        <p class="text-xs text-gray-500 mt-2">days in a row</p>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 font-medium">Longest Streak</p>
                        <p class="text-3xl font-bold text-blue-600">{{ analytics.overview.longest_streak }}</p>
                        <p class="text-xs text-gray-500 mt-2">all-time best</p>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 font-medium">Avg Per Week</p>
                        <p class="text-3xl font-bold text-purple-600">{{ analytics.overview.average_per_week }}</p>
                        <p class="text-xs text-gray-500 mt-2">{{ analytics.overview.average_per_month }} per month</p>
                    </div>
                </div>

                <!-- Monthly Trends -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">📊 12-Month Trend</h3>
                    <div class="space-y-4">
                        <div 
                            v-for="month in analytics.trends" 
                            :key="month.month"
                            class="flex items-center gap-4"
                        >
                            <div class="w-20 text-sm font-medium text-gray-700">{{ month.month_short }}</div>
                            <div class="flex-1">
                                <div class="w-full bg-gray-200 rounded-full h-8 overflow-hidden">
                                    <div 
                                        :style="{ width: month.percentage + '%' }"
                                        class="h-full bg-gradient-to-r from-green-400 to-green-600 flex items-center justify-center"
                                    >
                                        <span v-if="month.percentage > 20" class="text-xs font-bold text-white">
                                            {{ month.percentage }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-20 text-right text-sm text-gray-500">{{ month.completions }} days</div>
                        </div>
                    </div>
                </div>

                <!-- Best Days of Week -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">📅 Best Days of the Week</h3>
                        <div class="space-y-3">
                            <div 
                                v-for="(day, index) in analytics.best_days" 
                                :key="day.day"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">{{ getMedalEmoji(index) }}</span>
                                    <span class="font-medium text-gray-900">{{ day.day }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-600">{{ day.completions }} days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Breakdown -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">📆 Last 12 Weeks</h3>
                        <div class="space-y-2">
                            <div 
                                v-for="week in analytics.completion_by_week.slice(-12)" 
                                :key="week.week"
                                class="flex items-center gap-3"
                            >
                                <div class="w-24 text-xs font-medium text-gray-600">{{ week.week_start }}</div>
                                <div class="flex-1 bg-gray-200 rounded-full h-4 overflow-hidden">
                                    <div 
                                        :style="{ width: week.percentage + '%' }"
                                        class="h-full bg-blue-500"
                                    ></div>
                                </div>
                                <div class="w-10 text-right text-xs font-bold text-gray-600">{{ week.percentage }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">📈 Monthly Breakdown</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Month</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Completions</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Days in Month</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Completion %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="month in analytics.completion_by_month" :key="month.month" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ month.month }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ month.completions }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ month.days_in_month }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-full max-w-xs bg-gray-200 rounded-full h-2">
                                                <div 
                                                    :style="{ width: month.percentage + '%' }"
                                                    class="h-full bg-green-500 rounded-full"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-bold text-gray-600 whitespace-nowrap">{{ month.percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    analytics: {
        type: Object,
        required: true,
    },
});

const getMedalEmoji = (index) => {
    const medals = ['🥇', '🥈', '🥉'];
    return medals[index] || '⭐';
};
</script>

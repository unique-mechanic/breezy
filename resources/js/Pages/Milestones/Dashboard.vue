<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">🏆 Milestones Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Total Milestones</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.total_milestones }}</p>
                            </div>
                            <div class="text-4xl opacity-20">🎯</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Achieved</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats.achieved_milestones }}</p>
                            </div>
                            <div class="text-4xl opacity-20">✅</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">Achievement Rate</p>
                                <p class="text-3xl font-bold text-purple-600">{{ stats.achievement_rate }}%</p>
                            </div>
                            <div class="text-4xl opacity-20">📊</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 font-medium">In Progress</p>
                                <p class="text-3xl font-bold text-orange-600">{{ stats.total_milestones - stats.achieved_milestones }}</p>
                            </div>
                            <div class="text-4xl opacity-20">🚀</div>
                        </div>
                    </div>
                </div>

                <!-- Habits with Milestone Progress -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900">Habits & Progress</h3>
                    
                    <div v-if="habits.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="habit in habits" 
                            :key="habit.id"
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition"
                        >
                            <div class="flex items-center gap-3 mb-4">
                                <div 
                                    class="w-4 h-4 rounded"
                                    :style="{ backgroundColor: habit.color }"
                                ></div>
                                <h4 class="text-lg font-semibold text-gray-900">{{ habit.name }}</h4>
                            </div>

                            <div class="mb-4">
                                <p class="text-3xl font-bold text-orange-600">{{ habit.current_streak }}</p>
                                <p class="text-sm text-gray-600">day streak</p>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Milestones</span>
                                    <span class="text-sm font-bold text-gray-900">
                                        {{ habit.achieved_milestones }}/{{ habit.total_milestones }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        :style="{ width: (habit.achieved_milestones / habit.total_milestones) * 100 + '%' }"
                                        class="h-full bg-green-500 rounded-full transition"
                                    ></div>
                                </div>
                            </div>

                            <Link 
                                :href="route('milestones.show', habit.id)"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                            >
                                View Details →
                            </Link>
                        </div>
                    </div>

                    <div v-else class="bg-white p-8 rounded-lg shadow-sm text-center">
                        <p class="text-gray-500">Create habits to start tracking milestones!</p>
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
    stats: {
        type: Object,
        required: true,
    },
    habits: {
        type: Array,
        required: true,
    },
});
</script>

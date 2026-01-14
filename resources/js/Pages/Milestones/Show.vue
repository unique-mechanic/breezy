<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">{{ habit.name }} - Milestones</h2>
                <Link :href="route('milestones.dashboard')" class="text-blue-600 hover:text-blue-900">
                    ← All Milestones
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Newly Achieved Celebration -->
                <div 
                    v-if="newly_achieved.length > 0"
                    class="mb-8 bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-300 rounded-lg p-8"
                >
                    <div class="text-center">
                        <p class="text-2xl mb-4">🎉 Congratulations! 🎉</p>
                        <p class="text-gray-700 mb-6">You just unlocked:</p>
                        <div class="space-y-3">
                            <div v-for="(milestone, index) in newly_achieved" :key="index">
                                <p class="text-4xl mb-2">{{ milestone.icon }}</p>
                                <h3 class="text-2xl font-bold text-gray-900">{{ milestone.title }}</h3>
                                <p class="text-gray-600 mt-1">{{ milestone.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Streak Display -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 mb-8">
                    <div class="text-center">
                        <div class="text-6xl mb-4">🔥</div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-2">{{ progress.current_streak }} Day Streak</h2>
                        <p class="text-gray-600">Keep going to unlock more milestones!</p>
                    </div>
                </div>

                <!-- Achieved Milestones -->
                <div v-if="progress.achieved.length > 0" class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">✅ Achieved Milestones</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div 
                            v-for="milestone in progress.achieved" 
                            :key="milestone.id"
                            class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-lg p-6"
                        >
                            <div class="text-5xl mb-3">{{ milestone.icon }}</div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ milestone.title }}</h4>
                            <p class="text-gray-700 text-sm mb-4">{{ milestone.description }}</p>
                            <p class="text-xs text-gray-600">
                                Achieved {{ formatDate(milestone.achieved_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- In Progress Milestones -->
                <div v-if="progress.in_progress.length > 0" class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">🎯 In Progress</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div 
                            v-for="milestone in progress.in_progress" 
                            :key="milestone.id"
                            class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-300 rounded-lg p-6"
                        >
                            <div class="text-5xl mb-3">{{ milestone.icon }}</div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ milestone.title }}</h4>
                            <p class="text-gray-700 text-sm mb-4">{{ milestone.description }}</p>
                            <p class="text-xs font-bold text-blue-600">
                                You're on the edge - keep the streak alive!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Milestones -->
                <div v-if="progress.upcoming.length > 0">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">🚀 Upcoming Milestones</h3>
                    <div class="space-y-4">
                        <div 
                            v-for="milestone in progress.upcoming" 
                            :key="milestone.id"
                            class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="text-4xl">{{ milestone.icon }}</div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900">{{ milestone.title }}</h4>
                                        <p class="text-sm text-gray-600">{{ milestone.description }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Target: {{ milestone.target }} days
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-orange-600">{{ milestone.days_remaining }}</div>
                                    <p class="text-sm text-gray-600">days away</p>
                                    <div class="mt-3 w-20 bg-gray-200 rounded-full h-2">
                                        <div 
                                            :style="{ width: milestone.percentage_complete + '%' }"
                                            class="h-full bg-orange-500 rounded-full"
                                        ></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ milestone.percentage_complete }}%</p>
                                </div>
                            </div>
                        </div>
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
    habit: {
        type: Object,
        required: true,
    },
    progress: {
        type: Object,
        required: true,
    },
    newly_achieved: {
        type: Array,
        required: true,
    },
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

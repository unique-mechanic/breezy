<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ habit.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header with Stats -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-4">
              <div
                class="w-8 h-8 rounded"
                :style="{ backgroundColor: habit.color }"
              ></div>
              <div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ habit.name }}
                </h3>
                <p v-if="habit.description" class="text-gray-600 dark:text-gray-400 mt-1">
                  {{ habit.description }}
                </p>
                <div class="flex gap-4 mt-2 text-sm text-gray-500">
                  <span>{{ habit.category }}</span>
                  <span>{{ habit.frequency }}</span>
                  <span>Created {{ formatDate(habit.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="flex gap-2">
              <Link
                :href="`/analytics/habits/${habit.id}`"
                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-200"
              >
                📊 Analytics
              </Link>
              <Link
                href="/habits"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200"
              >
                ← Back
              </Link>
            </div>
          </div>

          <!-- Stats Grid -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-gray-50 dark:bg-gray-700 rounded p-4 text-center">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ habit.current_streak }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Current Streak
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded p-4 text-center">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ habit.longest_streak }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Longest Streak
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded p-4 text-center">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ habit.total_completions }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Total Completions
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded p-4 text-center">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ habit.completion_percentage }}%
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Completion Rate
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Recent Activity
          </h3>

          <div v-if="logs.data.length === 0" class="text-center py-8 text-gray-500">
            No activity yet
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="log in logs.data"
              :key="log.id"
              class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-3 h-3 rounded-full"
                  :class="log.completed ? 'bg-green-500' : 'bg-gray-300'"
                ></div>
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">
                    {{ formatDate(log.date) }}
                  </div>
                  <div v-if="log.notes" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ log.notes }}
                  </div>
                </div>
              </div>
              <span
                :class="log.completed ? 'text-green-600 dark:text-green-400' : 'text-gray-400'"
              >
                {{ log.completed ? '✓ Completed' : 'Skipped' }}
              </span>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="logs.links.length > 1" class="mt-6 flex justify-center gap-2">
            <template v-for="link in logs.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                class="px-3 py-1 border rounded text-sm"
                :class="
                  link.active
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300'
                "
              >
                {{ link.label }}
              </Link>
              <span v-else class="px-3 py-1 text-gray-400 text-sm">
                {{ link.label }}
              </span>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  habit: Object,
  logs: Object,
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>

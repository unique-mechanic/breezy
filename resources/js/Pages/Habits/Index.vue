<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Habit Tracker
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Add Habit Button -->
        <div class="mb-6">
          <Link
            href="/habits/create"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
          >
            + Add Habit
          </Link>
        </div>

        <!-- No Habits State -->
        <div v-if="habits.length === 0" class="text-center py-12">
          <p class="text-gray-500 dark:text-gray-400 mb-4">
            No habits yet. Create your first habit to get started!
          </p>
        </div>

        <!-- Habits Grid -->
        <div v-else class="space-y-8">
          <div
            v-for="habit in habits"
            :key="habit.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
          >
            <!-- Habit Header -->
            <div class="flex items-start justify-between mb-4">
              <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                  <span
                    class="w-4 h-4 rounded"
                    :style="{ backgroundColor: habit.color }"
                  ></span>
                  {{ habit.name }}
                </h3>
                <p v-if="habit.description" class="text-gray-600 dark:text-gray-400 mt-1">
                  {{ habit.description }}
                </p>
                <div class="flex gap-4 mt-2 text-sm">
                  <span class="text-gray-500">{{ habit.category }}</span>
                  <span class="text-gray-500">{{ habit.frequency }}</span>
                </div>
              </div>
              <Link
                :href="`/habits/${habit.id}/edit`"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200"
              >
                Edit
              </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-4 mb-6 text-center">
              <div class="bg-gray-50 dark:bg-gray-700 rounded p-3">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ habit.current_streak }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Current Streak
                </div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 rounded p-3">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ habit.longest_streak }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Longest Streak
                </div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 rounded p-3">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ habit.total_completions }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Completions
                </div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 rounded p-3">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ habit.completion_percentage }}%
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  This Year
                </div>
              </div>
            </div>

            <!-- GitHub-style Tiles Grid -->
            <div class="overflow-x-auto">
              <div class="inline-block">
                <!-- Weeks -->
                <div class="flex gap-1">
                  <div
                    v-for="(week, weekIndex) in getWeeks(habit.year_logs)"
                    :key="weekIndex"
                    class="flex flex-col gap-1"
                  >
                    <!-- Days in week -->
                    <div
                      v-for="dayIndex in 7"
                      :key="dayIndex"
                      class="relative group"
                    >
                      <button
                        @click="toggleDay(habit.id, week, dayIndex)"
                        :style="{
                          backgroundColor: getTileColor(habit, week, dayIndex, habit.color),
                        }"
                        class="w-3 h-3 rounded-sm border border-gray-300 dark:border-gray-600 hover:ring-1 hover:ring-offset-1 transition"
                        :aria-label="`${getDateForWeekDay(week, dayIndex)}`"
                      ></button>
                      <!-- Tooltip -->
                      <div
                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-900 dark:bg-gray-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10"
                      >
                        {{ getDateForWeekDay(week, dayIndex) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Legend -->
            <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
              <div class="flex gap-2 items-center">
                <span>Less</span>
                <div
                  class="w-2 h-2 rounded-sm"
                  style="background-color: #ebedf0"
                ></div>
                <div
                  class="w-2 h-2 rounded-sm"
                  :style="{ backgroundColor: adjustOpacity(habit.color, 0.3) }"
                ></div>
                <div
                  class="w-2 h-2 rounded-sm"
                  :style="{ backgroundColor: adjustOpacity(habit.color, 0.6) }"
                ></div>
                <div
                  class="w-2 h-2 rounded-sm"
                  :style="{ backgroundColor: habit.color }"
                ></div>
                <span>More</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  habits: Array,
});

const getWeeks = (yearLogs) => {
  const weeks = [];
  const startDate = new Date(new Date().getFullYear(), 0, 1);
  const endDate = new Date();

  for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
    const weekStart = new Date(d);
    weekStart.setDate(weekStart.getDate() - weekStart.getDay());

    const weekIndex = Math.floor((d - startDate) / (7 * 24 * 60 * 60 * 1000));
    if (!weeks[weekIndex]) {
      weeks[weekIndex] = new Date(weekStart);
    }
  }

  return weeks.filter(Boolean);
};

const getDateForWeekDay = (weekStart, dayIndex) => {
  const date = new Date(weekStart);
  date.setDate(date.getDate() + dayIndex - 1);
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  });
};

const getTileColor = (habit, week, dayIndex, baseColor) => {
  const date = new Date(week);
  date.setDate(date.getDate() + dayIndex - 1);
  const dateStr = date.toISOString().split('T')[0];

  const log = habit.year_logs[dateStr];

  if (!log) {
    return '#ebedf0';
  }

  if (log.completed) {
    return baseColor;
  }

  return adjustOpacity(baseColor, 0.2);
};

const adjustOpacity = (color, opacity) => {
  const hex = color.replace('#', '');
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, ${opacity})`;
};

const toggleDay = async (habitId, week, dayIndex) => {
  const date = new Date(week);
  date.setDate(date.getDate() + dayIndex - 1);
  const dateStr = date.toISOString().split('T')[0];

  try {
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
      // Reload the component or update the state
      window.location.reload();
    }
  } catch (error) {
    console.error('Error logging habit:', error);
  }
};
</script>

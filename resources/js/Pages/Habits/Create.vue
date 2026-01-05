<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ habit ? 'Edit Habit' : 'Create New Habit' }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Habit Name
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="e.g., Exercise, Read, Meditate"
                required
              />
              <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description (optional)
              </label>
              <textarea
                id="description"
                v-model="form.description"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Why are you building this habit?"
                rows="3"
              ></textarea>
              <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Category -->
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Category
              </label>
              <select
                id="category"
                v-model="form.category"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                required
              >
                <option value="health">Health & Fitness</option>
                <option value="learning">Learning</option>
                <option value="productivity">Productivity</option>
                <option value="mindfulness">Mindfulness</option>
                <option value="creativity">Creativity</option>
                <option value="social">Social</option>
                <option value="general">General</option>
              </select>
              <p v-if="form.errors.category" class="mt-2 text-sm text-red-600">
                {{ form.errors.category }}
              </p>
            </div>

            <!-- Frequency -->
            <div>
              <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Frequency
              </label>
              <select
                id="frequency"
                v-model="form.frequency"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                required
              >
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
              </select>
              <p v-if="form.errors.frequency" class="mt-2 text-sm text-red-600">
                {{ form.errors.frequency }}
              </p>
            </div>

            <!-- Color Picker -->
            <div>
              <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Color
              </label>
              <div class="flex gap-4 items-center mt-2">
                <input
                  id="color"
                  v-model="form.color"
                  type="color"
                  class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                  required
                />
                <div class="flex gap-2">
                  <button
                    v-for="color in colors"
                    :key="color"
                    @click="form.color = color"
                    type="button"
                    :style="{ backgroundColor: color }"
                    class="w-6 h-6 rounded border-2 transition"
                    :class="{
                      'border-gray-800 dark:border-white': form.color === color,
                      'border-transparent': form.color !== color,
                    }"
                  ></button>
                </div>
              </div>
              <p v-if="form.errors.color" class="mt-2 text-sm text-red-600">
                {{ form.errors.color }}
              </p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
              >
                {{ habit ? 'Update' : 'Create' }} Habit
              </button>
              <Link
                href="/habits"
                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition ease-in-out duration-150"
              >
                Cancel
              </Link>
              <button
                v-if="habit"
                @click="deleteHabit"
                type="button"
                :disabled="form.processing"
                class="ml-auto inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
              >
                Delete
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  habit: Object,
});

const colors = [
  '#3B82F6', // blue
  '#10B981', // green
  '#F59E0B', // amber
  '#EF4444', // red
  '#8B5CF6', // purple
  '#EC4899', // pink
  '#06B6D4', // cyan
];

const form = useForm({
  name: props.habit?.name || '',
  description: props.habit?.description || '',
  category: props.habit?.category || 'general',
  frequency: props.habit?.frequency || 'daily',
  color: props.habit?.color || '#3B82F6',
});

const submit = () => {
  if (props.habit) {
    form.patch(`/habits/${props.habit.id}`, {
      onSuccess: () => {
        // Handle success
      },
    });
  } else {
    form.post('/habits', {
      onSuccess: () => {
        // Handle success
      },
    });
  }
};

const deleteHabit = () => {
  if (confirm('Are you sure you want to delete this habit?')) {
    form.delete(`/habits/${props.habit.id}`);
  }
};
</script>

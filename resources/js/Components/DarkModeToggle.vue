<script setup>
import { ref } from 'vue';
import { toggleDarkMode, isDark, getIsLoading, getError } from '@/composables/useDarkMode';

const isDarkMode = isDark();
const isLoading = getIsLoading();
const error = getError();

async function handleToggle() {
    await toggleDarkMode();
}
</script>

<template>
    <div>
        <button
            @click="handleToggle"
            :disabled="isLoading"
            class="relative inline-flex items-center rounded-lg p-2 text-sm font-medium text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            title="Toggle dark mode"
        >
            <!-- Sun icon (shown in dark mode) -->
            <svg
                v-show="isDarkMode"
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 1.78a1 1 0 011.415 0l.707.707a1 1 0 01-1.415 1.415l-.707-.707a1 1 0 010-1.415zm2.828 2.828a1 1 0 011.415 0l.707.707a1 1 0 01-1.415 1.415l-.707-.707a1 1 0 010-1.415zM17 11a1 1 0 110 2h-1a1 1 0 110-2h1zm2.121-2.879a1 1 0 011.415 0l.707.707a1 1 0 01-1.415 1.415l-.707-.707a1 1 0 010-1.415zM10 18a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4.22-1.78a1 1 0 011.415 0l.707.707a1 1 0 01-1.415 1.415l-.707-.707a1 1 0 010-1.415zm-2.828-2.828a1 1 0 011.415 0l.707.707a1 1 0 01-1.415 1.415l-.707-.707a1 1 0 010-1.415zM3 11a1 1 0 110 2H2a1 1 0 110-2h1zm-2.121-2.879a1 1 0 011.415 0l.707.707a1 1 0 01-1.415 1.415l-.707-.707a1 1 0 010-1.415z"
                    clip-rule="evenodd"
                />
            </svg>

            <!-- Moon icon (shown in light mode) -->
            <svg
                v-show="!isDarkMode"
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                />
            </svg>
        </button>

        <!-- Error message (if any) -->
        <div
            v-if="error"
            class="mt-2 text-sm text-red-600"
        >
            {{ error }}
        </div>
    </div>
</template>

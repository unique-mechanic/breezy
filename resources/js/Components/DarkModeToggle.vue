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
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3v1m0 16v1m-8-9H3m18 0h-1m-2.636-5.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                />
            </svg>

            <!-- Moon icon (shown in light mode) -->
            <svg
                v-show="!isDarkMode"
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"
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

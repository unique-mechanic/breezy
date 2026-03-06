import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const isDarkMode = ref(false);
const isLoading = ref(false);
const error = ref(null);

/**
 * Initialize dark mode - check if feature is enabled and load user preference
 */
export function initializeDarkMode() {
    onMounted(async () => {
        try {
            // Check if dark mode feature is enabled
            const featureResponse = await axios.get('/api/features/dark_mode');
            
            if (!featureResponse.data.enabled && !featureResponse.data.enabled_for_user) {
                // Feature is disabled globally and for user - don't initialize dark mode
                return;
            }

            // Check localStorage for user preference
            const savedMode = localStorage.getItem('darkMode');
            if (savedMode !== null) {
                isDarkMode.value = savedMode === 'true';
            } else {
                // Check system preference
                isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            applyDarkMode();
        } catch (err) {
            console.error('Failed to initialize dark mode:', err);
        }
    });
}

/**
 * Apply or remove dark mode class to document
 */
function applyDarkMode() {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

/**
 * Toggle dark mode
 */
export async function toggleDarkMode() {
    try {
        isLoading.value = true;
        error.value = null;

        // Toggle on backend (saves user preference)
        await axios.post('/features/dark_mode/toggle-user');

        // Toggle locally
        isDarkMode.value = !isDarkMode.value;
        
        // Save to localStorage
        localStorage.setItem('darkMode', isDarkMode.value.toString());
        
        // Apply to DOM
        applyDarkMode();
    } catch (err) {
        error.value = 'Failed to toggle dark mode';
        console.error('Dark mode toggle error:', err);
    } finally {
        isLoading.value = false;
    }
}

/**
 * Get current dark mode state
 */
export function isDark() {
    return isDarkMode;
}

/**
 * Watch for changes and sync with localStorage
 */
watch(isDarkMode, (newValue) => {
    localStorage.setItem('darkMode', newValue.toString());
    applyDarkMode();
});

export function getIsLoading() {
    return isLoading;
}

export function getError() {
    return error;
}

<!-- resources/js/Pages/Telegram/CreateGroup.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Define props to receive flash messages from Laravel
defineProps({
    flash: Object,
});

// Initialize the form with Inertia's useForm
const form = useForm({
    group_name: '',
    description: '',
    admins: [
        { user_id: '', name: '' }
    ],
    permissions: {
        can_change_info: true,
        can_delete_messages: true,
        can_invite_users: true,
        can_restrict_members: true,
        can_pin_messages: true,
        can_promote_members: false
    }
});

// Permission definitions
const permissions = ref([
    { key: 'can_change_info', label: 'Change Group Info', description: 'Edit group name, photo, and description' },
    { key: 'can_delete_messages', label: 'Delete Messages', description: 'Remove any message in the group' },
    { key: 'can_invite_users', label: 'Invite Users', description: 'Add new members to the group' },
    { key: 'can_restrict_members', label: 'Restrict Members', description: 'Ban or limit member permissions' },
    { key: 'can_pin_messages', label: 'Pin Messages', description: 'Pin important messages' },
    { key: 'can_promote_members', label: 'Promote Members', description: 'Assign other users as admins' }
]);

// Methods
const addAdmin = () => {
    form.admins.push({ user_id: '', name: '' });
};

const removeAdmin = (index) => {
    form.admins.splice(index, 1);
};

const submitForm = () => {
    form.post(route('telegram.create-group'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form on success
            form.reset();
            form.admins = [{ user_id: '', name: '' }];
        }
    });
};
</script>

<template>
    <Head title="Create Telegram Group" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-3 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.693-1.653-1.124-2.678-1.8-1.185-.781-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.14.122.099.155.232.171.326.016.094.036.308.02.475z"/>
                </svg>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create Telegram Group
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Success Alert -->
                <div v-if="flash?.success || $page.props.flash?.success" class="mb-6">
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                        <div class="flex">
                            <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-green-800 font-medium">{{ flash?.success || $page.props.flash?.success }}</p>
                        </div>
                    </div>
                </div>

                <!-- Error Alert -->
                <div v-if="flash?.error || $page.props.flash?.error" class="mb-6">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                        <div class="flex">
                            <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-red-800 font-medium">{{ flash?.error || $page.props.flash?.error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Errors -->
                <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mb-6">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                        <div class="flex">
                            <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-red-800 font-medium mb-2">Please fix the following errors:</p>
                                <ul class="list-disc list-inside text-red-700 space-y-1">
                                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Group Name -->
                            <div>
                                <label for="group_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Group Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="group_name"
                                    v-model="form.group_name"
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none"
                                    placeholder="Enter group name">
                                <p class="mt-2 text-sm text-gray-500">This will be the name of your Telegram group</p>
                                <p v-if="form.errors.group_name" class="mt-2 text-sm text-red-600">{{ form.errors.group_name }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Group Description <span class="text-gray-400">(Optional)</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none resize-none"
                                    placeholder="Enter group description"></textarea>
                            </div>

                            <!-- Admins Section -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-4">
                                    Admins to Add <span class="text-red-500">*</span>
                                </label>

                                <div class="space-y-4">
                                    <div
                                        v-for="(admin, index) in form.admins"
                                        :key="index"
                                        class="bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500 p-4 rounded-lg">
                                        <div class="flex gap-3">
                                            <div class="flex-1">
                                                <input
                                                    type="text"
                                                    v-model="admin.user_id"
                                                    required
                                                    class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none"
                                                    placeholder="User ID or @username">
                                            </div>
                                            <div class="flex-1">
                                                <input
                                                    type="text"
                                                    v-model="admin.name"
                                                    class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none"
                                                    placeholder="Admin Name (optional)">
                                            </div>
                                            <button
                                                v-if="form.admins.length > 1"
                                                type="button"
                                                @click="removeAdmin(index)"
                                                class="px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="addAdmin"
                                    class="mt-4 px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add Another Admin
                                </button>

                                <p class="mt-3 text-sm text-gray-500">Enter Telegram User ID (numeric) or username (e.g., @username)</p>
                            </div>

                            <!-- Permissions -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-4">
                                    Admin Permissions
                                </label>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label
                                        v-for="permission in permissions"
                                        :key="permission.key"
                                        class="flex items-start p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-purple-50 transition-colors border-2 border-transparent hover:border-purple-200">
                                        <input
                                            type="checkbox"
                                            v-model="form.permissions[permission.key]"
                                            class="w-5 h-5 text-purple-600 rounded focus:ring-2 focus:ring-purple-500 cursor-pointer mt-0.5">
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900">{{ permission.label }}</span>
                                            <p class="text-xs text-gray-500">{{ permission.description }}</p>
                                        </div>
                                    </label>
                                </div>

                                <p class="mt-3 text-sm text-gray-500">Select permissions for the admins</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-lg transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-purple-500 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center text-lg shadow-lg">
                                    <svg v-if="!form.processing" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <svg v-else class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Creating Group...' : 'Create Group' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-200">
                        <p class="text-sm text-gray-600 text-center">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            All admins will receive notifications once the group is created
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Dashboard" />


    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight inline-block mr-2 text-gray-800"
            >
                Dashboard
            </h2>
            <button >
                <a href="/create_telegram_form"
                    class="text-xl font-semibold leading-tight inline-block mr-2 text-gray-800 hover:text-blue-500 hover:underline"
                >
                    Telegram
                </a>
            </button>

        </template>

        <!--
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        You're logged in!
                    </div>
                </div>
            </div>
        </div>
        -->
    </AuthenticatedLayout>


</template>

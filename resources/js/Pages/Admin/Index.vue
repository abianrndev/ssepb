<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recent_estimations: { type: Array, default: () => [] },
    trend: { type: Array, default: () => [] },
    top_users: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Active Engineers</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_engineers }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Total Estimations</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_estimations }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Quick Cast</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.quick_count }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Road</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.road_count }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Building</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.building_count }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Recent Estimations</h3>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="px-3 py-2 text-left">Module</th>
                                    <th class="px-3 py-2 text-left">Project</th>
                                    <th class="px-3 py-2 text-left">User</th>
                                    <th class="px-3 py-2 text-left">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in recent_estimations" :key="row.module + row.created_at" class="border-b last:border-0 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ row.module }}</td>
                                    <td class="px-3 py-2">{{ row.nama_proyek }}</td>
                                    <td class="px-3 py-2">{{ row.user }}</td>
                                    <td class="px-3 py-2">{{ row.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="!recent_estimations.length" class="py-4 text-center text-gray-500">Belum ada data.</p>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Top Users</h3>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="px-3 py-2 text-left">User</th>
                                    <th class="px-3 py-2 text-left">Total Estimations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in top_users" :key="row.name" class="border-b last:border-0 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ row.name }}</td>
                                    <td class="px-3 py-2">{{ row.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="!top_users.length" class="py-4 text-center text-gray-500">Belum ada data.</p>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Trend 7 Hari Terakhir</h3>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div v-for="row in trend" :key="row.date" class="rounded-lg border p-3 dark:border-gray-700">
                            <p class="text-xs text-gray-500">{{ row.date }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ row.total }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
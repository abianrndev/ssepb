<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">--</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Active Engineers</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">--</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <p class="text-sm text-gray-500">Open Tasks</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">--</p>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        <Link href="/admin/users" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                            Manage Users
                        </Link>
                        <Link href="/admin/pricing" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                            Manage Pricing
                        </Link>
                        <Link href="/admin/roles" class="rounded-lg bg-slate-700 px-4 py-2 text-white hover:bg-slate-800">
                            Manage Roles
                        </Link>
                        <Link href="/admin/reports" class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                            View Reports
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
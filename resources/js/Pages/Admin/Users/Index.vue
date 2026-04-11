<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: { type: Array, default: () => [] },
    availableRoles: { type: Array, default: () => ['admin', 'engineer'] },
});

const page = usePage();

const form = useForm({
    role: '',
});

const updateRole = (userId, role) => {
    form.role = role;
    form.patch(`/admin/users/${userId}/role`, {
        preserveScroll: true,
        onFinish: () => form.reset('role'),
    });
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Users
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">
                    {{ page.props.flash.error }}
                </div>

                <div class="overflow-x-auto rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="px-3 py-2 text-left">Name</th>
                                <th class="px-3 py-2 text-left">Email</th>
                                <th class="px-3 py-2 text-left">Current Role</th>
                                <th class="px-3 py-2 text-left">Change Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in users" :key="u.id" class="border-b last:border-0 dark:border-gray-700">
                                <td class="px-3 py-2">{{ u.name }}</td>
                                <td class="px-3 py-2">{{ u.email }}</td>
                                <td class="px-3 py-2">
                                    <span class="rounded bg-gray-100 px-2 py-1 dark:bg-gray-700">
                                        {{ u.roles?.[0] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex gap-2">
                                        <button
                                            v-for="r in availableRoles"
                                            :key="`${u.id}-${r}`"
                                            @click="updateRole(u.id, r)"
                                            class="rounded-md px-3 py-1 text-white"
                                            :class="r === 'admin' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                                            :disabled="form.processing"
                                        >
                                            Set {{ r }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p v-if="!users.length" class="py-6 text-center text-gray-500">No users found.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
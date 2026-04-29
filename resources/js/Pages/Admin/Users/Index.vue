<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: { type: Array, default: () => [] },
    availableRoles: { type: Array, default: () => ['admin', 'engineer'] },
});

const page = usePage();

const roleForm = useForm({ role: '' });
const createForm = useForm({ name: '', email: '', password: '', password_confirmation: '' });
const updateForm = useForm({ name: '', email: '' });
const statusForm = useForm({ is_active: true });

const updateRole = (userId, role) => {
    roleForm.role = role;
    roleForm.patch(`/admin/users/${userId}/role`, {
        preserveScroll: true,
        onFinish: () => roleForm.reset('role'),
    });
};

const updateUser = (user) => {
    updateForm.name = user.name;
    updateForm.email = user.email;

    updateForm.patch(`/admin/users/${user.id}`, {
        preserveScroll: true,
        onFinish: () => updateForm.reset(),
    });
};

const createUser = () => {
    createForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const toggleStatus = (user) => {
    statusForm.is_active = !user.is_active;

    statusForm.patch(`/admin/users/${user.id}/status`, {
        preserveScroll: true,
        onFinish: () => statusForm.reset(),
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-100 p-3 text-green-800">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-lg bg-red-100 p-3 text-red-800">
                    {{ page.props.flash.error }}
                </div>

                <!-- FORM TAMBAH ENGINEER -->
                <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Tambah Akun Engineer</h3>
                    <div class="grid gap-4 md:grid-cols-4">
                        <input v-model="createForm.name" class="rounded-md border-gray-300 text-sm" placeholder="Nama" />
                        <input v-model="createForm.email" class="rounded-md border-gray-300 text-sm" placeholder="Email" />
                        <input v-model="createForm.password" type="password" class="rounded-md border-gray-300 text-sm" placeholder="Password" />
                        <input v-model="createForm.password_confirmation" type="password" class="rounded-md border-gray-300 text-sm" placeholder="Konfirmasi Password" />
                    </div>
                    <button
                        type="button"
                        @click.stop.prevent="createUser"
                        class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                        :disabled="createForm.processing"
                    >
                        Simpan
                    </button>
                </div>

                <!-- TABEL USER -->
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
                                            type="button"
                                            v-for="r in availableRoles"
                                            :key="`${u.id}-${r}`"
                                            @click.stop.prevent="updateRole(u.id, r)"
                                            class="rounded-md px-3 py-1 text-white"
                                            :class="r === 'admin' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                                            :disabled="roleForm.processing"
                                        >
                                            Set {{ r }}
                                        </button>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <span
                                        class="rounded px-2 py-1 text-xs"
                                        :class="u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <button
                                        type="button"
                                        @click.stop.prevent="toggleStatus(u)"
                                        class="rounded-md px-3 py-1.5 text-white"
                                        :class="u.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                                        :disabled="statusForm.processing"
                                    >
                                        {{ u.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
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
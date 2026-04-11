<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    prices: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const form = useForm({
    harga_per_m3: 0,
    is_active: true,
});

const formatRupiah = (value) => {
    const n = Number(value || 0);
    return new Intl.NumberFormat('id-ID').format(n);
};

const updatePrice = (row) => {
    form.harga_per_m3 = Number(row.harga_per_m3 || 0);
    form.is_active = !!row.is_active;

    form.patch(`/admin/pricing/${row.id}`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Manage Pricing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Pricing Beton
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
                    {{ page.props.flash.success }}
                </div>

                <div class="overflow-x-auto rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="px-3 py-2 text-left">Mutu Beton</th>
                                <th class="px-3 py-2 text-left">Harga / m³ (Rp)</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in prices" :key="row.id" class="border-b last:border-0 dark:border-gray-700">
                                <td class="px-3 py-2 font-medium">{{ row.mutu_beton }}</td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        min="0"
                                        class="w-48 rounded-md border-gray-300 text-sm"
                                        v-model.number="row.harga_per_m3"
                                    />
                                    <div class="mt-1 text-xs text-gray-500">
                                        Format: {{ formatRupiah(row.harga_per_m3) }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" v-model="row.is_active" />
                                        <span>{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </label>
                                </td>
                                <td class="px-3 py-2">
                                    <button
                                        @click="updatePrice(row)"
                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700"
                                        :disabled="form.processing"
                                    >
                                        Simpan
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p v-if="!prices.length" class="py-6 text-center text-gray-500">
                        Belum ada data harga.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
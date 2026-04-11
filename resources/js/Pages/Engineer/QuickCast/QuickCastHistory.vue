<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    histories: { type: Array, default: () => [] },
});

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));
</script>

<template>
    <Head title="Riwayat Cor Cepat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Riwayat Estimasi Cor Cepat
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-x-auto rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="px-3 py-2 text-left">Tanggal</th>
                                <th class="px-3 py-2 text-left">Nama Proyek</th>
                                <th class="px-3 py-2 text-left">Mutu</th>
                                <th class="px-3 py-2 text-left">Total (m³)</th>
                                <th class="px-3 py-2 text-left">Estimasi Harga</th>
                                <th class="px-3 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in histories" :key="row.id" class="border-b last:border-0 dark:border-gray-700">
                                <td class="px-3 py-2">{{ row.created_at }}</td>
                                <td class="px-3 py-2">{{ row.nama_proyek || '-' }}</td>
                                <td class="px-3 py-2">{{ row.mutu_rekomendasi }}</td>
                                <td class="px-3 py-2">{{ row.total_akhir_m3 }}</td>
                                <td class="px-3 py-2">Rp {{ rupiah(row.estimasi_harga_total) }}</td>
                                <td class="px-3 py-2">
                                    <Link :href="`/engineer/quick-cast/history/${row.id}`" class="rounded bg-indigo-600 px-3 py-1 text-white">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p v-if="!histories.length" class="py-6 text-center text-gray-500">
                        Belum ada riwayat estimasi.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
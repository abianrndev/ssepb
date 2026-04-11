<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
});

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));
</script>

<template>
    <Head :title="`Detail Estimasi #${item.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detail Estimasi Cor Cepat #{{ item.id }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
                    <div class="mb-4">
                        <Link href="/engineer/quick-cast/history" class="text-indigo-600 hover:underline">
                            ← Kembali ke Riwayat
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                        <div><b>Tanggal:</b> {{ item.created_at }}</div>
                        <div><b>Nama Proyek:</b> {{ item.nama_proyek || '-' }}</div>
                        <div><b>Lokasi:</b> {{ item.lokasi_proyek || '-' }}</div>
                        <div><b>Beban:</b> {{ item.beban_penggunaan }}</div>
                        <div><b>Mutu:</b> {{ item.mutu_rekomendasi }}</div>
                        <div><b>Waste:</b> {{ item.waste_percent }}%</div>

                        <div><b>Panjang:</b> {{ item.panjang_m }} m</div>
                        <div><b>Lebar:</b> {{ item.lebar_m }} m</div>
                        <div><b>Tebal:</b> {{ item.tebal_cm }} cm ({{ item.tebal_m }} m)</div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700" />

                    <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                        <div><b>Volume Kotor:</b> {{ item.volume_kotor }} m³</div>
                        <div><b>Volume Bersih:</b> {{ item.volume_bersih }} m³</div>
                        <div><b>Waste Volume:</b> {{ item.waste_volume }} m³</div>
                        <div><b>Total Akhir:</b> {{ item.total_akhir_m3 }} m³</div>
                        <div><b>Harga / m³:</b> Rp {{ rupiah(item.harga_per_m3) }}</div>
                        <div><b>Estimasi Harga Total:</b> Rp {{ rupiah(item.estimasi_harga_total) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
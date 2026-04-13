<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
});

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));
const labelMetode = (m) => (m === 'total' ? 'Total Langsung' : 'Per Segmen');
</script>

<template>
    <Head :title="`Detail Road #${item.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Detail Estimasi Beton Jalan #{{ item.id }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <div>
                    <Link href="/engineer/road/history" class="text-indigo-600 hover:underline">
                        ← Kembali ke Riwayat Beton Jalan
                    </Link>
                    <a :href="`/engineer/road/history/${item.id}/export-pdf`"
                        class="rounded-md bg-red-600 px-3 py-1.5 text-white hover:bg-red-700"
                        >
                        Download PDF
                    </a>
                </div>

                <!-- RINGKASAN -->
                <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Ringkasan Utama</h3>

                    <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                        <div><b>Tanggal:</b> {{ item.created_at }}</div>
                        <div><b>Metode:</b> {{ labelMetode(item.metode_input) }}</div>
                        <div><b>Nama Proyek:</b> {{ item.nama_proyek || '-' }}</div>
                        <div><b>Lokasi:</b> {{ item.lokasi_proyek || '-' }}</div>
                        <div><b>Mutu Beton:</b> {{ item.mutu_beton }}</div>
                        <div><b>Waste:</b> {{ item.waste_percent }}%</div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700" />

                    <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-3">
                        <div><b>Jumlah Lajur:</b> {{ item.jumlah_lajur }}</div>
                        <div><b>Lebar/Lajur:</b> {{ item.lebar_per_lajur_m }} m</div>
                        <div><b>Lebar Total:</b> {{ item.lebar_total_m }} m</div>
                        <div><b>Bahu Kiri:</b> {{ item.bahu_kiri_m }} m</div>
                        <div><b>Bahu Kanan:</b> {{ item.bahu_kanan_m }} m</div>
                        <div><b>Tebal Beton:</b> {{ item.tebal_beton_m }} m</div>
                        <div><b>Panjang Total:</b> {{ item.panjang_total_m ?? '-' }} m</div>
                    </div>

                    <hr class="my-4 border-gray-200 dark:border-gray-700" />

                    <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                        <div><b>Volume Kotor:</b> {{ item.volume_kotor }} m³</div>
                        <div><b>Volume Pengurang:</b> {{ item.volume_pengurang }} m³</div>
                        <div><b>Volume Bersih:</b> {{ item.volume_bersih }} m³</div>
                        <div><b>Waste Volume:</b> {{ item.waste_volume }} m³</div>
                        <div><b>Total Akhir:</b> {{ item.total_akhir_m3 }} m³</div>
                        <div><b>Harga/m³:</b> Rp {{ rupiah(item.harga_per_m3) }}</div>
                    </div>

                    <div class="mt-4 rounded-lg bg-indigo-50 p-3 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300">
                        <b>Estimasi Harga Total: Rp {{ rupiah(item.estimasi_harga_total) }}</b>
                    </div>
                </div>

                <!-- SEGMEN -->
                <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Daftar Segmen</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="px-3 py-2 text-left">STA Awal</th>
                                    <th class="px-3 py-2 text-left">STA Akhir</th>
                                    <th class="px-3 py-2 text-left">Panjang</th>
                                    <th class="px-3 py-2 text-left">Lebar</th>
                                    <th class="px-3 py-2 text-left">Tebal</th>
                                    <th class="px-3 py-2 text-left">Volume</th>
                                    <th class="px-3 py-2 text-left">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in item.segments" :key="s.id" class="border-b last:border-0 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ s.sta_awal || '-' }}</td>
                                    <td class="px-3 py-2">{{ s.sta_akhir || '-' }}</td>
                                    <td class="px-3 py-2">{{ s.panjang_m }} m</td>
                                    <td class="px-3 py-2">{{ s.lebar_m }} m</td>
                                    <td class="px-3 py-2">{{ s.tebal_m }} m</td>
                                    <td class="px-3 py-2">{{ s.volume_m3 }} m³</td>
                                    <td class="px-3 py-2">{{ s.keterangan || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-if="!item.segments.length" class="py-4 text-sm text-gray-500">
                            Tidak ada data segmen (kemungkinan mode total langsung).
                        </p>
                    </div>
                </div>

                <!-- PENGURANG -->
                <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Daftar Area Pengurang</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="px-3 py-2 text-left">Jenis Bukaan</th>
                                    <th class="px-3 py-2 text-left">Panjang</th>
                                    <th class="px-3 py-2 text-left">Lebar</th>
                                    <th class="px-3 py-2 text-left">Jumlah</th>
                                    <th class="px-3 py-2 text-left">Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in item.deductions" :key="d.id" class="border-b last:border-0 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ d.jenis_bukaan }}</td>
                                    <td class="px-3 py-2">{{ d.panjang_m }} m</td>
                                    <td class="px-3 py-2">{{ d.lebar_m }} m</td>
                                    <td class="px-3 py-2">{{ d.jumlah }}</td>
                                    <td class="px-3 py-2">{{ d.volume_m3 }} m³</td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-if="!item.deductions.length" class="py-4 text-sm text-gray-500">
                            Tidak ada area pengurang.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
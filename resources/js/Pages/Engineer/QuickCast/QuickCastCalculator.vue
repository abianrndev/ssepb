<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    result: { type: Object, default: null },
    success: { type: String, default: '' },
});

const form = useForm({
    nama_proyek: '',
    lokasi_proyek: '',
    panjang_m: '',
    lebar_m: '',
    tebal_cm: '',
    beban_penggunaan: 'sedang',
    waste_percent: 5,
});

const submit = () => {
    form.post('/engineer/quick-cast');
};

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));
</script>

<template>
    <Head title="Quick Cast Calculator" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Cor Cepat (Awam) - Estimasi Beton
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold">Input Data</h3>
                    <input v-model="form.nama_proyek" type="text" placeholder="Nama proyek" class="mb-2 w-full rounded-md border-gray-300 text-sm" />
                    <input v-model="form.lokasi_proyek" type="text" placeholder="Lokasi proyek" class="mb-2 w-full rounded-md border-gray-300 text-sm" />
                    <input v-model.number="form.panjang_m" type="number" step="0.01" min="0" placeholder="Panjang (m)" class="mb-2 w-full rounded-md border-gray-300 text-sm" />
                    <input v-model.number="form.lebar_m" type="number" step="0.01" min="0" placeholder="Lebar (m)" class="mb-2 w-full rounded-md border-gray-300 text-sm" />
                    <input v-model.number="form.tebal_cm" type="number" step="0.01" min="0" placeholder="Tebal (cm)" class="mb-2 w-full rounded-md border-gray-300 text-sm" />
                    <select v-model="form.beban_penggunaan" class="mb-2 w-full rounded-md border-gray-300 text-sm">
                        <option value="ringan">Ringan</option>
                        <option value="sedang">Sedang</option>
                        <option value="berat">Berat</option>
                    </select>
                    <input v-model.number="form.waste_percent" type="number" step="0.01" min="0" max="50" placeholder="Waste (%)" class="mb-3 w-full rounded-md border-gray-300 text-sm" />
                    <button @click="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-white">Hitung & Simpan</button>
                </div>

                <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold">Hasil Estimasi</h3>

                    <div v-if="success" class="mb-3 rounded bg-green-100 p-2 text-sm text-green-800">{{ success }}</div>

                    <div v-if="result" class="space-y-2 text-sm">
                        <div>Mutu Rekomendasi: <b>{{ result.mutu_rekomendasi }}</b></div>
                        <div>Harga / m³: <b>Rp {{ rupiah(result.harga_per_m3) }}</b></div>
                        <div>Volume Kotor: <b>{{ result.volume_kotor }} m³</b></div>
                        <div>Waste: <b>{{ result.waste_volume }} m³</b></div>
                        <div>Total Akhir: <b>{{ result.total_akhir_m3 }} m³</b></div>
                        <div>Estimasi Harga Total: <b>Rp {{ rupiah(result.estimasi_harga_total) }}</b></div>
                    </div>
                    <p v-else class="text-sm text-gray-500">Belum ada hasil.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
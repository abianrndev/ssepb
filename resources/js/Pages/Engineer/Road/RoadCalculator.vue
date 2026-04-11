<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    mutuOptions: { type: Array, default: () => [] },
    result: { type: Object, default: null },
    success: { type: String, default: '' },
});

const form = useForm({
    // 1) Informasi proyek
    nama_proyek: '',
    lokasi_proyek: '',

    // 2) Parameter umum
    metode_input: 'segmen', // total | segmen
    jumlah_lajur: 2,
    lebar_per_lajur_m: 3,
    bahu_kiri_m: 0.5,
    bahu_kanan_m: 0.5,
    tebal_beton_m: 0.2,
    mutu_beton: props.mutuOptions?.[0] ?? 'K-300',
    waste_percent: 5,

    // 3A) Mode total
    panjang_total_m: '',

    // 3B) Mode segmen
    segments: [
        {
            sta_awal: '0+000',
            sta_akhir: '0+100',
            panjang_m: 100,
            lebar_m: 7,
            tebal_m: 0.2,
            keterangan: '',
        },
    ],

    // 4) Pengurang
    deductions: [
        {
            jenis_bukaan: '',
            panjang_m: '',
            lebar_m: '',
            jumlah: 1,
        },
    ],
});

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));

const lebarTotal = computed(() => {
    const jl = Number(form.jumlah_lajur || 0);
    const lpl = Number(form.lebar_per_lajur_m || 0);
    const bk = Number(form.bahu_kiri_m || 0);
    const bkn = Number(form.bahu_kanan_m || 0);
    return jl * lpl + bk + bkn;
});

const previewVolumeKotor = computed(() => {
    const t = Number(form.tebal_beton_m || 0);

    if (form.metode_input === 'total') {
        const p = Number(form.panjang_total_m || 0);
        return p > 0 && lebarTotal.value > 0 && t > 0 ? p * lebarTotal.value * t : 0;
    }

    return (form.segments || []).reduce((sum, s) => {
        const p = Number(s.panjang_m || 0);
        const l = Number(s.lebar_m || lebarTotal.value || 0);
        const ts = Number(s.tebal_m || t || 0);
        if (p <= 0 || l <= 0 || ts <= 0) return sum;
        return sum + p * l * ts;
    }, 0);
});

const previewVolumePengurang = computed(() => {
    const t = Number(form.tebal_beton_m || 0);
    return (form.deductions || []).reduce((sum, d) => {
        const p = Number(d.panjang_m || 0);
        const l = Number(d.lebar_m || 0);
        const j = Number(d.jumlah || 0);
        if (p <= 0 || l <= 0 || j <= 0 || t <= 0) return sum;
        return sum + p * l * t * j;
    }, 0);
});

const previewVolumeBersih = computed(() => Math.max(previewVolumeKotor.value - previewVolumePengurang.value, 0));
const previewWaste = computed(() => previewVolumeBersih.value * (Number(form.waste_percent || 0) / 100));
const previewTotalAkhir = computed(() => previewVolumeBersih.value + previewWaste.value);

const addSegment = () => {
    form.segments.push({
        sta_awal: '',
        sta_akhir: '',
        panjang_m: '',
        lebar_m: lebarTotal.value || '',
        tebal_m: form.tebal_beton_m || '',
        keterangan: '',
    });
};

const removeSegment = (index) => {
    form.segments.splice(index, 1);
};

const addDeduction = () => {
    form.deductions.push({
        jenis_bukaan: '',
        panjang_m: '',
        lebar_m: '',
        jumlah: 1,
    });
};

const removeDeduction = (index) => {
    form.deductions.splice(index, 1);
};

const applyDefaultToSegments = () => {
    form.segments = (form.segments || []).map((s) => ({
        ...s,
        lebar_m: s.lebar_m || lebarTotal.value,
        tebal_m: s.tebal_m || form.tebal_beton_m,
    }));
};

const submit = () => {
    form.post('/engineer/road', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Road Calculator - Beton Jalan" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Estimasi Penggunaan Beton Jalan
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Hitung kebutuhan beton jalan dalam m³ + estimasi harga. Cocok untuk mode total atau per segmen STA.
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                <!-- LEFT: FORM -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- INFO BOX -->
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-200">
                        <p class="font-semibold">Panduan cepat isi form:</p>
                        <ol class="mt-2 list-decimal space-y-1 pl-5">
                            <li>Isi informasi proyek & parameter umum jalan.</li>
                            <li>Pilih metode input: <b>Total Langsung</b> atau <b>Per Segmen (STA)</b>.</li>
                            <li>Tambahkan area pengurang jika ada manhole/saluran yang tidak dicor.</li>
                            <li>Klik <b>Hitung & Simpan</b> untuk melihat hasil akhir dan menyimpan histori.</li>
                        </ol>
                    </div>

                    <!-- 1) INFORMASI PROYEK -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">1) Informasi Proyek</h3>
                        <p class="mt-1 text-sm text-gray-500">Data identitas proyek untuk pelacakan histori & laporan.</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Proyek</label>
                                <input v-model="form.nama_proyek" type="text" placeholder="Contoh: Pengecoran jalan pahlawan" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Lokasi Proyek</label>
                                <input v-model="form.lokasi_proyek" type="text" placeholder="Contoh: Madiun, Jawa Timur" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>
                        </div>
                    </div>

                    <!-- 2) PARAMETER UMUM -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">2) Parameter Umum Jalan</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Parameter ini dipakai sebagai nilai default perhitungan beton jalan.
                        </p>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Metode Input</label>
                                <select v-model="form.metode_input" class="w-full rounded-md border-gray-300 text-sm">
                                    <option value="total">Total Langsung</option>
                                    <option value="segmen">Per Segmen (STA)</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    Total: satu panjang total. Segmen: per ruas STA (lebih detail).
                                </p>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Mutu Beton</label>
                                <select v-model="form.mutu_beton" class="w-full rounded-md border-gray-300 text-sm">
                                    <option v-for="m in mutuOptions" :key="m" :value="m">{{ m }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah Lajur</label>
                                <input v-model.number="form.jumlah_lajur" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Lebar per Lajur (m)</label>
                                <input v-model.number="form.lebar_per_lajur_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Bahu Kiri (m)</label>
                                <input v-model.number="form.bahu_kiri_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Bahu Kanan (m)</label>
                                <input v-model.number="form.bahu_kanan_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Tebal Beton (m)</label>
                                <input v-model.number="form.tebal_beton_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                                <p class="mt-1 text-xs text-gray-500">Contoh: 0.20 m = 20 cm.</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Waste Factor (%)</label>
                                <input v-model.number="form.waste_percent" type="number" step="0.01" min="0" max="50" class="w-full rounded-md border-gray-300 text-sm" />
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-700/40">
                            <p class="text-gray-700 dark:text-gray-200">
                                Lebar efektif otomatis:
                                <b>{{ lebarTotal.toFixed(2) }} m</b>
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Rumus: (jumlah lajur × lebar per lajur) + bahu kiri + bahu kanan.
                            </p>
                        </div>
                    </div>

                    <!-- 3) INPUT DIMENSI -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">3) Input Dimensi Jalan</h3>

                        <!-- MODE TOTAL -->
                        <div v-if="form.metode_input === 'total'" class="mt-3">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Panjang Jalan Total (m)
                            </label>
                            <input v-model.number="form.panjang_total_m" type="number" step="0.01" min="0" placeholder="Contoh: 500" class="w-full rounded-md border-gray-300 text-sm" />
                            <p class="mt-1 text-xs text-gray-500">
                                Volume kotor = panjang total × lebar efektif × tebal beton.
                            </p>
                        </div>

                        <!-- MODE SEGMEN -->
                        <div v-else class="mt-3">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Input per segmen STA</p>
                                    <p class="text-xs text-gray-500">Isi panjang per segmen. Lebar & tebal boleh kosong (otomatis pakai nilai default).</p>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="applyDefaultToSegments" type="button" class="rounded bg-gray-700 px-3 py-1.5 text-xs text-white hover:bg-gray-800">
                                        Terapkan default ke segmen kosong
                                    </button>
                                    <button @click="addSegment" type="button" class="rounded bg-indigo-600 px-3 py-1.5 text-xs text-white hover:bg-indigo-700">
                                        + Tambah Segmen
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(seg, i) in form.segments"
                                    :key="i"
                                    class="rounded-lg border p-3 dark:border-gray-700"
                                >
                                    <div class="mb-2 text-xs font-semibold text-gray-500">Segmen #{{ i + 1 }}</div>

                                    <div class="grid grid-cols-1 gap-2 md:grid-cols-6">
                                        <div>
                                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">STA Awal</label>
                                            <input v-model="seg.sta_awal" type="text" placeholder="0+000" class="w-full rounded-md border-gray-300 text-sm" />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">STA Akhir</label>
                                            <input v-model="seg.sta_akhir" type="text" placeholder="0+100" class="w-full rounded-md border-gray-300 text-sm" />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Panjang (m)</label>
                                            <input v-model.number="seg.panjang_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Lebar (m)</label>
                                            <input v-model.number="seg.lebar_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Tebal (m)</label>
                                            <input v-model.number="seg.tebal_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Aksi</label>
                                            <button @click="removeSegment(i)" type="button" class="w-full rounded bg-red-600 px-3 py-2 text-xs text-white hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Keterangan (opsional)</label>
                                        <input v-model="seg.keterangan" type="text" placeholder="Contoh: tikungan dekat gerbang" class="w-full rounded-md border-gray-300 text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4) PENGURANG -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">4) Area Pengurang (Opsional)</h3>
                                <p class="text-sm text-gray-500">Untuk area yang tidak dicor: manhole, utilitas, saluran, dll.</p>
                            </div>
                            <button @click="addDeduction" type="button" class="rounded bg-gray-700 px-3 py-1.5 text-xs text-white hover:bg-gray-800">
                                + Tambah Bukaan
                            </button>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(d, i) in form.deductions"
                                :key="i"
                                class="grid grid-cols-1 gap-2 rounded-lg border p-3 md:grid-cols-5 dark:border-gray-700"
                            >
                                <div>
                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Jenis Bukaan</label>
                                    <input v-model="d.jenis_bukaan" type="text" placeholder="Manhole / Saluran" class="w-full rounded-md border-gray-300 text-sm" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Panjang (m)</label>
                                    <input v-model.number="d.panjang_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Lebar (m)</label>
                                    <input v-model.number="d.lebar_m" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 text-sm" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Jumlah</label>
                                    <input v-model.number="d.jumlah" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Aksi</label>
                                    <button @click="removeDeduction(i)" type="button" class="w-full rounded bg-red-600 px-3 py-2 text-xs text-white hover:bg-red-700">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <button
                            @click="submit"
                            type="button"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {{ form.processing ? 'Menghitung...' : 'Hitung & Simpan Estimasi' }}
                        </button>
                    </div>
                </div>

                <!-- RIGHT: RESULT + PREVIEW -->
                <div class="space-y-6">
                    <!-- PREVIEW REALTIME -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Realtime</h3>
                        <p class="mt-1 text-xs text-gray-500">Ini preview cepat dari input saat ini (sebelum submit).</p>

                        <div class="mt-4 space-y-2 text-sm text-gray-800 dark:text-gray-200">
                            <div>Volume Kotor: <b>{{ previewVolumeKotor.toFixed(4) }} m³</b></div>
                            <div>Volume Pengurang: <b>{{ previewVolumePengurang.toFixed(4) }} m³</b></div>
                            <div>Volume Bersih: <b>{{ previewVolumeBersih.toFixed(4) }} m³</b></div>
                            <div>Waste: <b>{{ previewWaste.toFixed(4) }} m³</b></div>
                            <div>Total Akhir: <b>{{ previewTotalAkhir.toFixed(4) }} m³</b></div>
                        </div>
                    </div>

                    <!-- SERVER RESULT -->
                    <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hasil Estimasi Tersimpan</h3>

                        <div v-if="success" class="mt-3 rounded bg-green-100 p-2 text-sm text-green-800">
                            {{ success }}
                        </div>

                        <div v-if="result" class="mt-4 space-y-2 text-sm text-gray-800 dark:text-gray-200">
                            <div>Mutu Beton: <b>{{ result.mutu_beton }}</b></div>
                            <div>Harga / m³: <b>Rp {{ rupiah(result.harga_per_m3) }}</b></div>
                            <hr class="my-2 border-gray-200 dark:border-gray-700" />
                            <div>Volume Kotor: <b>{{ result.volume_kotor }} m³</b></div>
                            <div>Volume Pengurang: <b>{{ result.volume_pengurang }} m³</b></div>
                            <div>Volume Bersih: <b>{{ result.volume_bersih }} m³</b></div>
                            <div>Waste: <b>{{ result.waste_volume }} m³</b></div>
                            <div>Total Akhir: <b>{{ result.total_akhir_m3 }} m³</b></div>
                            <hr class="my-2 border-gray-200 dark:border-gray-700" />
                            <div class="text-base">
                                Estimasi Harga Total:
                                <b class="text-indigo-600">Rp {{ rupiah(result.estimasi_harga_total) }}</b>
                            </div>
                        </div>

                        <p v-else class="mt-3 text-sm text-gray-500">
                            Belum ada hasil tersimpan. Isi form lalu klik <b>Hitung & Simpan Estimasi</b>.
                        </p>
                    </div>

                    <!-- NOTES -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-200">
                        <p class="font-semibold">Catatan teknis:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            <li>Semua satuan panjang/lebar/tebal menggunakan meter (m).</li>
                            <li>Untuk input tebal 20 cm, masukkan <b>0.20</b>.</li>
                            <li>Area pengurang dihitung menggunakan tebal beton utama.</li>
                            <li>Hasil ini estimasi awal, tetap perlu verifikasi engineer lapangan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
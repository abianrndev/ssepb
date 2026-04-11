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
  nama_proyek: '',
  lokasi_proyek: '',
  jumlah_lantai: '',
  mutu_beton: props.mutuOptions?.[0] ?? 'K-300',
  waste_percent: '',

  items: [
    { jenis_item: 'pelat', nama_item: 'Pelat Lt.1', jumlah: 1, panjang_m: 10, lebar_m: 8, tebal_m: 0.12, keterangan: '' },
    { jenis_item: 'balok', nama_item: 'Balok Utama', jumlah: 12, panjang_m: 4, lebar_m: 0.25, tebal_m: 0.4, keterangan: '' },
    { jenis_item: 'kolom', nama_item: 'Kolom', jumlah: 16, panjang_m: 0.3, lebar_m: 0.3, tebal_m: 3.5, keterangan: '' },
  ],

  deductions: [],
});

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));

const rowVolume = (r) => {
  const j = Number(r.jumlah || 0);
  const p = Number(r.panjang_m || 0);
  const l = Number(r.lebar_m || 0);
  const t = Number(r.tebal_m || 0);
  if (j <= 0 || p <= 0 || l <= 0 || t <= 0) return 0;
  return j * p * l * t;
};

const volumeKotor = computed(() => (form.items || []).reduce((s, r) => s + rowVolume(r), 0));
const volumePengurang = computed(() => (form.deductions || []).reduce((s, r) => s + rowVolume(r), 0));
const volumeBersih = computed(() => Math.max(volumeKotor.value - volumePengurang.value, 0));
const wasteVolume = computed(() => volumeBersih.value * (Number(form.waste_percent || 0) / 100));
const totalAkhir = computed(() => volumeBersih.value + wasteVolume.value);

const addItem = () => {
  form.items.push({ jenis_item: 'lainnya', nama_item: '', jumlah: 1, panjang_m: '', lebar_m: '', tebal_m: '', keterangan: '' });
};
const delItem = (i) => form.items.splice(i, 1);

const addDeduction = () => {
  form.deductions.push({ jenis_pengurang: 'void', nama_item: '', jumlah: 1, panjang_m: '', lebar_m: '', tebal_m: '', keterangan: '' });
};
const delDeduction = (i) => form.deductions.splice(i, 1);

const submit = () => form.post('/engineer/building', { preserveScroll: true });
</script>

<template>
  <Head title="Cor Rumah/Gedung" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Kalkulator Cor Rumah/Gedung</h2>
          <p class="text-xs text-gray-500">Ringkas, cepat, dan langsung terlihat hasilnya.</p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
        <!-- KIRI: FORM -->
        <div class="lg:col-span-8 space-y-4">
          <!-- Header form compact -->
          <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-5">
              <input v-model="form.nama_proyek" type="text" placeholder="Nama proyek" class="rounded-md border-gray-300 text-sm" />
              <input v-model="form.lokasi_proyek" type="text" placeholder="Lokasi" class="rounded-md border-gray-300 text-sm" />
              <input v-model.number="form.jumlah_lantai" type="number" min="1" placeholder="Lantai" class="rounded-md border-gray-300 text-sm" />
              <select v-model="form.mutu_beton" class="rounded-md border-gray-300 text-sm">
                <option v-for="m in mutuOptions" :key="m" :value="m">{{ m }}</option>
              </select>
              <input v-model.number="form.waste_percent" type="number" min="0" max="50" step="0.01" placeholder="Waste (%)" class="rounded-md border-gray-300 text-sm" />
            </div>
            <p class="mt-2 text-[11px] text-gray-500">
              Rumus tiap baris: <b>jumlah × panjang × lebar × tebal</b> (m).
            </p>
          </div>

          <!-- Item struktur -->
          <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
            <div class="mb-2 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item Struktur</h3>
              <button @click="addItem" type="button" class="rounded bg-indigo-600 px-2.5 py-1 text-xs text-white">+ Item</button>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full text-xs">
                <thead>
                  <tr class="border-b dark:border-gray-700">
                    <th class="px-2 py-2 text-left">Jenis</th>
                    <th class="px-2 py-2 text-left">Nama</th>
                    <th class="px-2 py-2 text-left">Jml</th>
                    <th class="px-2 py-2 text-left">P</th>
                    <th class="px-2 py-2 text-left">L</th>
                    <th class="px-2 py-2 text-left">T</th>
                    <th class="px-2 py-2 text-left">Vol</th>
                    <th class="px-2 py-2 text-left"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(r, i) in form.items" :key="i" class="border-b last:border-0 dark:border-gray-700">
                    <td class="px-2 py-1.5">
                      <select v-model="r.jenis_item" class="w-24 rounded border-gray-300 text-xs">
                        <option value="pelat">Pelat</option>
                        <option value="balok">Balok</option>
                        <option value="kolom">Kolom</option>
                        <option value="sloof">Sloof</option>
                        <option value="lainnya">Lain</option>
                      </select>
                    </td>
                    <td class="px-2 py-1.5"><input v-model="r.nama_item" type="text" class="w-36 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.jumlah" type="number" min="1" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.panjang_m" type="number" step="0.01" min="0" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.lebar_m" type="number" step="0.01" min="0" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.tebal_m" type="number" step="0.01" min="0" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5 font-semibold">{{ rowVolume(r).toFixed(3) }}</td>
                    <td class="px-2 py-1.5">
                      <button @click="delItem(i)" type="button" class="rounded bg-red-600 px-2 py-1 text-[11px] text-white">Hapus</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pengurang -->
          <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
            <div class="mb-2 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pengurang (Opsional)</h3>
              <button @click="addDeduction" type="button" class="rounded bg-gray-700 px-2.5 py-1 text-xs text-white">+ Pengurang</button>
            </div>

            <div v-if="form.deductions.length" class="overflow-x-auto">
              <table class="min-w-full text-xs">
                <thead>
                  <tr class="border-b dark:border-gray-700">
                    <th class="px-2 py-2 text-left">Jenis</th>
                    <th class="px-2 py-2 text-left">Jml</th>
                    <th class="px-2 py-2 text-left">P</th>
                    <th class="px-2 py-2 text-left">L</th>
                    <th class="px-2 py-2 text-left">T</th>
                    <th class="px-2 py-2 text-left">Vol</th>
                    <th class="px-2 py-2 text-left"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(r, i) in form.deductions" :key="i" class="border-b last:border-0 dark:border-gray-700">
                    <td class="px-2 py-1.5"><input v-model="r.jenis_pengurang" type="text" class="w-28 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.jumlah" type="number" min="1" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.panjang_m" type="number" step="0.01" min="0" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.lebar_m" type="number" step="0.01" min="0" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5"><input v-model.number="r.tebal_m" type="number" step="0.01" min="0" class="w-16 rounded border-gray-300 text-xs" /></td>
                    <td class="px-2 py-1.5 font-semibold">{{ rowVolume(r).toFixed(3) }}</td>
                    <td class="px-2 py-1.5">
                      <button @click="delDeduction(i)" type="button" class="rounded bg-red-600 px-2 py-1 text-[11px] text-white">Hapus</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="text-xs text-gray-500">Tidak ada pengurang.</p>
          </div>

          <div>
            <button @click="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white">
              {{ form.processing ? 'Menghitung...' : 'Hitung & Simpan' }}
            </button>
          </div>
        </div>

        <!-- KANAN: RINGKAS -->
        <div class="lg:col-span-4 space-y-4">
          <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Cepat</h3>
            <div class="mt-2 space-y-1 text-sm">
              <div>Vol. Kotor: <b>{{ volumeKotor.toFixed(3) }} m³</b></div>
              <div>Pengurang: <b>{{ volumePengurang.toFixed(3) }} m³</b></div>
              <div>Bersih: <b>{{ volumeBersih.toFixed(3) }} m³</b></div>
              <div>Waste: <b>{{ wasteVolume.toFixed(3) }} m³</b></div>
              <div>Total: <b>{{ totalAkhir.toFixed(3) }} m³</b></div>
            </div>
          </div>

          <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Hasil Tersimpan</h3>
            <div v-if="success" class="mt-2 rounded bg-green-100 p-2 text-xs text-green-800">{{ success }}</div>

            <div v-if="result" class="mt-2 space-y-1 text-sm">
              <div>Mutu: <b>{{ result.mutu_beton }}</b></div>
              <div>Harga/m³: <b>Rp {{ rupiah(result.harga_per_m3) }}</b></div>
              <div>Total m³: <b>{{ result.total_akhir_m3 }}</b></div>
              <div>Total Harga: <b class="text-indigo-600">Rp {{ rupiah(result.estimasi_harga_total) }}</b></div>
            </div>
            <p v-else class="mt-2 text-xs text-gray-500">Belum ada hasil.</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
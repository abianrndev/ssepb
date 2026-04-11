<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  item: { type: Object, required: true },
});

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));
</script>

<template>
  <Head :title="`Detail Building #${item.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
        Detail Estimasi Cor Rumah/Gedung #{{ item.id }}
      </h2>
    </template>

    <div class="py-8">
      <div class="flex items-center gap-2">
        <Link href="/engineer/building/history" class="text-indigo-600 hover:underline">
          ← Kembali ke Riwayat
        </Link>

        <a :href="`/engineer/building/history/${item.id}/export-pdf`"
          class="rounded-md bg-red-600 px-3 py-1.5 text-white hover:bg-red-700">
          Download PDF
        </a>
      </div>

        <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
          <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Ringkasan</h3>

          <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
            <div><b>Tanggal:</b> {{ item.created_at }}</div>
            <div><b>Nama Proyek:</b> {{ item.nama_proyek || '-' }}</div>
            <div><b>Lokasi:</b> {{ item.lokasi_proyek || '-' }}</div>
            <div><b>Jumlah Lantai:</b> {{ item.jumlah_lantai }}</div>
            <div><b>Mutu Beton:</b> {{ item.mutu_beton }}</div>
            <div><b>Waste:</b> {{ item.waste_percent }}%</div>
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

        <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
          <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Item Struktur</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b dark:border-gray-700">
                  <th class="px-3 py-2 text-left">Jenis</th>
                  <th class="px-3 py-2 text-left">Nama</th>
                  <th class="px-3 py-2 text-left">Jumlah</th>
                  <th class="px-3 py-2 text-left">P</th>
                  <th class="px-3 py-2 text-left">L</th>
                  <th class="px-3 py-2 text-left">T</th>
                  <th class="px-3 py-2 text-left">Volume</th>
                  <th class="px-3 py-2 text-left">Ket.</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in item.items" :key="r.id" class="border-b last:border-0 dark:border-gray-700">
                  <td class="px-3 py-2">{{ r.jenis_item }}</td>
                  <td class="px-3 py-2">{{ r.nama_item || '-' }}</td>
                  <td class="px-3 py-2">{{ r.jumlah }}</td>
                  <td class="px-3 py-2">{{ r.panjang_m }}</td>
                  <td class="px-3 py-2">{{ r.lebar_m }}</td>
                  <td class="px-3 py-2">{{ r.tebal_m }}</td>
                  <td class="px-3 py-2">{{ r.volume_m3 }}</td>
                  <td class="px-3 py-2">{{ r.keterangan || '-' }}</td>
                </tr>
              </tbody>
            </table>

            <p v-if="!item.items.length" class="py-4 text-sm text-gray-500">
              Tidak ada item struktur.
            </p>
          </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow dark:bg-gray-800">
          <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Pengurang</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b dark:border-gray-700">
                  <th class="px-3 py-2 text-left">Jenis</th>
                  <th class="px-3 py-2 text-left">Jumlah</th>
                  <th class="px-3 py-2 text-left">P</th>
                  <th class="px-3 py-2 text-left">L</th>
                  <th class="px-3 py-2 text-left">T</th>
                  <th class="px-3 py-2 text-left">Volume</th>
                  <th class="px-3 py-2 text-left">Ket.</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in item.deductions" :key="r.id" class="border-b last:border-0 dark:border-gray-700">
                  <td class="px-3 py-2">{{ r.jenis_pengurang }}</td>
                  <td class="px-3 py-2">{{ r.jumlah }}</td>
                  <td class="px-3 py-2">{{ r.panjang_m }}</td>
                  <td class="px-3 py-2">{{ r.lebar_m }}</td>
                  <td class="px-3 py-2">{{ r.tebal_m }}</td>
                  <td class="px-3 py-2">{{ r.volume_m3 }}</td>
                  <td class="px-3 py-2">{{ r.keterangan || '-' }}</td>
                </tr>
              </tbody>
            </table>

            <p v-if="!item.deductions.length" class="py-4 text-sm text-gray-500">
              Tidak ada data pengurang.
            </p>
          </div>
        </div>
      </div>
  </AuthenticatedLayout>
</template>
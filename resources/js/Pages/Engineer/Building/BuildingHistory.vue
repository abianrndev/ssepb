<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
  histories: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({ date_from: '', date_to: '', mutu_beton: '' }),
  },
});

const form = useForm({
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  mutu_beton: props.filters.mutu_beton || '',
});

const applyFilter = () => {
  router.get('/engineer/building/history', form.data(), {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilter = () => {
  form.date_from = '';
  form.date_to = '';
  form.mutu_beton = '';
  applyFilter();
};

const rupiah = (n) => new Intl.NumberFormat('id-ID').format(Number(n || 0));
</script>

<template>
  <Head title="Riwayat Cor Rumah/Gedung" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
        Riwayat Estimasi Cor Rumah/Gedung
      </h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <input v-model="form.date_from" type="date" class="rounded-md border-gray-300 text-sm" />
            <input v-model="form.date_to" type="date" class="rounded-md border-gray-300 text-sm" />
            <input v-model="form.mutu_beton" type="text" placeholder="Mutu (contoh K-300)" class="rounded-md border-gray-300 text-sm" />

            <div class="flex gap-2">
              <button @click="applyFilter" class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700">
                Terapkan
              </button>
              <button @click="resetFilter" class="rounded-md bg-gray-600 px-3 py-2 text-sm text-white hover:bg-gray-700">
                Reset
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white p-4 shadow dark:bg-gray-800">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b dark:border-gray-700">
                <th class="px-3 py-2 text-left">Tanggal</th>
                <th class="px-3 py-2 text-left">Proyek</th>
                <th class="px-3 py-2 text-left">Lokasi</th>
                <th class="px-3 py-2 text-left">Lantai</th>
                <th class="px-3 py-2 text-left">Mutu</th>
                <th class="px-3 py-2 text-left">Total m³</th>
                <th class="px-3 py-2 text-left">Estimasi Harga</th>
                <th class="px-3 py-2 text-left">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in histories" :key="row.id" class="border-b last:border-0 dark:border-gray-700">
                <td class="px-3 py-2">{{ row.created_at }}</td>
                <td class="px-3 py-2">{{ row.nama_proyek || '-' }}</td>
                <td class="px-3 py-2">{{ row.lokasi_proyek || '-' }}</td>
                <td class="px-3 py-2">{{ row.jumlah_lantai }}</td>
                <td class="px-3 py-2">{{ row.mutu_beton }}</td>
                <td class="px-3 py-2">{{ row.total_akhir_m3 }}</td>
                <td class="px-3 py-2">Rp {{ rupiah(row.estimasi_harga_total) }}</td>
                <td class="px-3 py-2">
                  <Link :href="`/engineer/building/history/${row.id}`" class="rounded-md bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700">
                    Detail
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>

          <p v-if="!histories.length" class="py-6 text-center text-gray-500">
            Belum ada riwayat estimasi cor rumah/gedung.
          </p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
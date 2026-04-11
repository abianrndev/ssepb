<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Engineer Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Engineer Dashboard</h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-800">
          <p class="mb-4 text-gray-700 dark:text-gray-300">Pilih menu:</p>

          <div class="flex flex-wrap gap-3">
            <Link href="/engineer/quick-cast" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Cor Cepat</Link>
            <Link href="/engineer/quick-cast/history" class="rounded-lg bg-gray-700 px-4 py-2 text-white hover:bg-gray-800">Riwayat Cor Cepat</Link>

            <Link href="/engineer/road" class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Beton Jalan</Link>
            <Link href="/engineer/road/history" class="rounded-lg bg-emerald-800 px-4 py-2 text-white hover:bg-emerald-900">Riwayat Beton Jalan</Link>

            <Link href="/engineer/building" class="rounded-lg bg-violet-600 px-4 py-2 text-white hover:bg-violet-700">Cor Rumah/Gedung</Link>
            <Link href="/engineer/building/history" class="rounded-lg bg-violet-800 px-4 py-2 text-white hover:bg-violet-900">Riwayat Rumah/Gedung</Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
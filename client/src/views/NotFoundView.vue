<script setup lang="ts">
import { computed } from 'vue'
import { currentUser } from '../services/auth'

const home = computed(() => {
  switch (currentUser.value?.role) {
    case 'admin':
      return '/admin/manajemen-pengiriman'
    case 'checker':
      return '/checker'
    case 'courier':
      return '/courier'
    default:
      return '/'
  }
})
</script>

<template>
  <main class="flex min-h-screen items-center justify-center px-4 py-12">
    <section class="live-panel w-full max-w-lg text-center">
      <img
        src="/images/anteraja-pharma-logo.png"
        alt="Anteraja Pharma"
        width="165"
        height="68"
        class="mx-auto mb-8 h-14 w-auto object-contain"
      />
      <p class="text-sm font-bold uppercase tracking-[0.18em] text-primary">
        Halaman tidak ditemukan
      </p>
      <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900">
        404
      </h1>
      <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-600">
        Tautan yang dibuka tidak tersedia atau sudah berubah. Kembali ke beranda
        portal untuk melanjutkan alur kerja.
      </p>
      <RouterLink :to="home" class="live-button live-primary mt-7">
        Kembali ke beranda
      </RouterLink>
    </section>
  </main>
</template>

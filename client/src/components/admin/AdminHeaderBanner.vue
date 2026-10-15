<script setup lang="ts">
const emit = defineEmits<{
  (e: 'export-csv'): void
  (e: 'print-labels'): void
}>()
</script>

<template>
  <div class="mb-space-lg flex flex-col md:flex-row md:items-end justify-between gap-space-md">
    <div>
      <div class="flex flex-wrap items-center gap-space-xs mb-space-xs">
        <span class="font-label-sm text-label-sm text-primary uppercase tracking-widest bg-primary-fixed/60 px-2.5 py-1 rounded-full font-bold shadow-2xs">
          Admin Dispatcher &amp; Compliance Portal
        </span>
        <span class="font-label-sm text-label-sm text-on-surface-variant">•</span>
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Regulated CPOB / GDP</span>
        <div class="flex items-center gap-1.5 ml-space-xs bg-surface-container-low px-2 py-0.5 rounded-full border border-surface-container">
          <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
          <span class="font-label-sm text-label-sm text-secondary font-semibold">Live Sync Active</span>
        </div>
      </div>

      <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight font-bold">
        Manajemen Pengiriman Kargo Farmasi
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-3xl leading-relaxed">
        Pendaftaran pengiriman kargo rantai dingin baru, alokasi penugasan Checker QC awal di gudang, serta pemantauan menyeluruh status pengiriman aktif.
      </p>
    </div>

    <div class="flex items-center gap-space-sm shrink-0">
      <button
        type="button"
        @click="emit('export-csv')"
        class="bg-surface-container-lowest hover:bg-surface-container-low text-on-surface font-label-lg text-label-lg px-4 py-2.5 rounded-lg shadow-sm transition-all flex items-center gap-2 border border-surface-container/60 cursor-pointer font-bold active:scale-95"
      >
        <span class="material-symbols-outlined text-lg">download</span>
        Export Manifest .CSV
      </button>

      <button
        type="button"
        @click="emit('print-labels')"
        class="bg-primary hover:bg-on-primary-fixed-variant text-on-primary font-label-lg text-label-lg px-4 py-2.5 rounded-lg shadow-sm transition-all flex items-center gap-2 cursor-pointer font-bold active:scale-95"
      >
        <span class="material-symbols-outlined text-lg">print</span>
        Cetak Label Barcode
      </button>
    </div>
  </div>
</template>

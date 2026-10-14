<script setup lang="ts">
interface Props {
  coolerBoxTemp?: number
  completedCount?: number
  totalCount?: number
}

withDefaults(defineProps<Props>(), {
  coolerBoxTemp: 4.2,
  completedCount: 2,
  totalCount: 5,
})
</script>

<template>
  <!-- Header & Live Operational Snapshot -->
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6 border-b border-slate-100">
    <div class="flex flex-col gap-1.5">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary-fixed text-primary-container font-label-sm">
          <span class="material-symbols-outlined text-[16px]">local_shipping</span>
        </span>
        <span class="font-label-sm uppercase tracking-wider text-primary-container font-bold">
          Portal Satria Pharma • Manifest Aktif
        </span>
      </div>
      <h1 class="font-headline-lg text-on-surface font-bold tracking-tight">
        Daftar Tugas Pengiriman Saya
      </h1>
      <p class="font-body-md text-on-surface-variant max-w-2xl">
        Kelola dan proses kargo farmasi rantai dingin yang ditugaskan kepada Anda hari ini sesuai mandat standar CPOB &amp; CDOB Kemenkes RI.
      </p>
    </div>

    <!-- Quick Stat Chips -->
    <div class="flex items-center gap-3 shrink-0">
      <div class="bg-surface-container-lowest px-4 py-2.5 rounded-xl shadow-xs border border-surface-container/60 flex items-center gap-3">
        <span class="material-symbols-outlined text-secondary text-[24px]">thermostat</span>
        <div class="flex flex-col">
          <span class="font-label-sm text-on-surface-variant uppercase font-semibold">Suhu Cooler Box</span>
          <span class="font-label-lg font-bold text-on-surface">
            {{ coolerBoxTemp }}°C <span class="font-body-sm font-normal text-secondary">Optimal</span>
          </span>
        </div>
      </div>

      <div class="bg-surface-container-lowest px-4 py-2.5 rounded-xl shadow-xs border border-surface-container/60 flex items-center gap-3">
        <span class="material-symbols-outlined text-primary-container text-[24px]">route</span>
        <div class="flex flex-col">
          <span class="font-label-sm text-on-surface-variant uppercase font-semibold">Target Antar</span>
          <span class="font-label-lg font-bold text-on-surface">
            {{ completedCount }} / {{ totalCount }} <span class="font-body-sm font-normal text-on-surface-variant">Selesai</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

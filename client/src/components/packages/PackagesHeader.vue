<script setup lang="ts">
import { ref } from 'vue'

const emit = defineEmits<{
  (e: 'refresh'): void
  (e: 'downloadRekap'): void
}>()

const isRefreshing = ref(false)

function handleRefresh() {
  isRefreshing.value = true
  emit('refresh')
  setTimeout(() => {
    isRefreshing.value = false
  }, 800)
}
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">
    <div class="flex flex-col gap-space-xs">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant">
        <router-link to="/" class="hover:text-primary transition-colors cursor-pointer">Portal Rantai Dingin</router-link>
        <span class="text-outline-variant">/</span>
        <span class="text-primary font-semibold">Paket Konsinyasi Aktif</span>
      </div>

      <!-- Heading & Description -->
      <h1 class="font-display-lg text-[28px] sm:text-[34px] leading-tight text-on-surface font-extrabold tracking-tight">
        Daftar Kiriman Farmasi Aktif
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl leading-relaxed">
        Pantau integritas rantai dingin (<span class="font-bold text-on-surface">cold-chain</span>), telemetri sensor suhu IoT real-time, serta kalkulasi estimasi kedatangan kargo obat &amp; vaksin biologis untuk <span class="font-bold text-on-surface">RS Medika Sehat Jakarta</span>.
      </p>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-space-sm self-start md:self-auto shrink-0">
      <button
        type="button"
        @click="handleRefresh"
        class="flex items-center gap-space-xs px-space-md py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold transition-all shadow-xs cursor-pointer active:scale-95 border border-surface-container-high"
      >
        <span
          class="material-symbols-outlined text-[18px] text-primary transition-transform duration-500"
          :class="{ 'rotate-180': isRefreshing }"
        >
          sync
        </span>
        <span>Sinkronisasi Sensor</span>
      </button>

      <button
        type="button"
        @click="emit('downloadRekap')"
        class="flex items-center gap-space-xs px-space-md py-2 rounded-xl bg-primary-container hover:opacity-95 text-on-primary font-label-md text-label-md font-semibold transition-all shadow-xs cursor-pointer active:scale-95"
      >
        <span class="material-symbols-outlined text-[18px]">download</span>
        <span>Unduh Rekap CDOB</span>
      </button>
    </div>
  </div>
</template>

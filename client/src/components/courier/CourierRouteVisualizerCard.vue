<script setup lang="ts">
interface Props {
  corridorName?: string
  trafficStatus?: string
  ambientTemp?: number
  distanceKm?: number
  durationMin?: number
  mapImageUrl?: string
}

withDefaults(defineProps<Props>(), {
  corridorName: 'Koridor Pengiriman Jalur Cepat (Bypass Salemba)',
  trafficStatus: 'Lalu Lintas: Lancar Hijau',
  ambientTemp: 31,
  distanceKm: 9.8,
  durationMin: 24,
  mapImageUrl: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCxwMz0bs7MVcb0Ewdc0Nh_JmmbKNokVYVP43XlwadlTgI3V21J1WlBeCtVHCQWDFPrKPuzzTDPxocLlrKE7Fr34xDbLMKLCC8iZBgkvsf0aoLLkt53HOuY3wyioRVLkTnlp_lvVTr6wayvRMzDIQ21Z3PZHySj-SbGeKbwmo7YzrAG5JCeLfuTR0Gw6tXS5BezFL2I2Khj3MfiXTXWEinO7oszJ9Uwsy6Woaju8ggi4eGkejPPOLg1JQ',
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm flex flex-col gap-3 border border-surface-container/60">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px] text-secondary">alt_route</span>
        <h3 class="font-label-lg text-on-surface font-bold">{{ corridorName }}</h3>
      </div>
      <span class="font-body-sm text-on-surface-variant font-medium">
        {{ trafficStatus }} • Suhu Lingkungan: {{ ambientTemp }}°C
      </span>
    </div>

    <!-- Map Canvas with Route Overlay -->
    <div
      class="w-full h-44 rounded-xl bg-surface-container-high relative overflow-hidden flex items-end p-4 bg-cover bg-center border border-surface-container/60 shadow-inner"
      :style="{ backgroundImage: `url('${mapImageUrl}')` }"
    >
      <!-- Subtle dark gradient vignette for text readability -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent pointer-events-none"></div>

      <!-- Distance and Estimated Time Chip -->
      <div class="relative z-10 bg-surface-container-lowest/90 backdrop-blur-md px-3 py-1.5 rounded-lg flex items-center gap-2 shadow-sm border border-white/60">
        <span class="material-symbols-outlined text-[16px] text-primary-container">navigation</span>
        <span class="font-label-sm text-on-surface font-semibold">
          Jarak: {{ distanceKm }} km • Waktu Tempuh Est: {{ durationMin }} mnt
        </span>
      </div>
    </div>
  </div>
</template>

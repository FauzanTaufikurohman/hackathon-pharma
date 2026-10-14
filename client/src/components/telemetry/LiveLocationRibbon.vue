<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    locationText?: string
    updatedAgo?: string
  }>(),
  {
    locationText: 'Tol Dalam Kota - Cawang (Menuju RS Medika Sehat, Kuningan)',
    updatedAgo: 'Baru saja',
  }
)
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-xs border border-surface-container mb-space-lg flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
    <div class="flex items-center gap-space-md">
      <div class="w-10 h-10 rounded-xl bg-primary-container/10 flex items-center justify-center text-primary-container shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-[24px]">pin_drop</span>
      </div>
      <div>
        <div class="font-label-sm text-[11px] uppercase text-on-surface-variant font-bold tracking-wider">
          Lokasi Terkini Armada (last_location)
        </div>
        <div class="font-headline-sm text-headline-sm text-on-surface font-bold mt-0.5">
          {{ locationText }}
        </div>
      </div>
    </div>

    <div class="flex items-center gap-space-xs self-start sm:self-auto bg-surface-container-low px-3 py-1.5 rounded-lg text-on-surface-variant font-label-md text-label-md border border-surface-container">
      <span class="material-symbols-outlined text-[16px] text-secondary">sync</span>
      <span>Pembaruan Telemetri GPS: <strong class="text-on-surface">{{ updatedAgo }}</strong></span>
    </div>
  </div>
</template>

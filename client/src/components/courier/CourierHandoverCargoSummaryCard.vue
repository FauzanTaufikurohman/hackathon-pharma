<script setup lang="ts">
interface Props {
  cargoName?: string
  batchNumber?: string
  expiryDate?: string
  colliCount?: string
  originHub?: string
  destinationFacility?: string
}

withDefaults(defineProps<Props>(), {
  cargoName: 'Vaksin Meningococcal Konjugat ACYW',
  batchNumber: '#MCV-2026-X12',
  expiryDate: 'Nov 2027',
  colliCount: '2 Koli Terverifikasi',
  originHub: 'Hub Pharma Cawang (DC)',
  destinationFacility: 'RS Medika Sehat Jakarta',
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm space-y-4 border border-surface-container/60">
    <div class="flex items-center justify-between">
      <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold">
        Ringkasan Kargo Cold-Chain
      </span>
      <span class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm font-bold shadow-2xs">
        {{ colliCount }}
      </span>
    </div>

    <div class="flex items-center gap-4 p-4 rounded-xl bg-surface-container-low border border-surface-container/40">
      <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-2xl">vaccines</span>
      </div>
      <div class="min-w-0 flex-1">
        <h3 class="font-headline-sm text-headline-sm text-on-surface truncate font-bold">
          {{ cargoName }}
        </h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
          Batch: <span class="font-mono font-semibold text-on-surface">{{ batchNumber }}</span> • Exp: {{ expiryDate }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3 pt-2">
      <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">Asal Titik Muat</span>
        <p class="font-label-md text-label-md text-on-surface font-semibold truncate mt-0.5">
          {{ originHub }}
        </p>
      </div>
      <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">Fasilitas Tujuan</span>
        <p class="font-label-md text-label-md text-on-surface font-semibold truncate mt-0.5">
          {{ destinationFacility }}
        </p>
      </div>
    </div>
  </div>
</template>

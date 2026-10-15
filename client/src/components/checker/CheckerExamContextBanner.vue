<script setup lang="ts">
interface Props {
  awb?: string
  batchId?: string
  commodityTitle?: string
  packageCountStr?: string
  originFacility?: string
  destinationFacility?: string
  tempRangeStr?: string
  inspectorName?: string
  inspectorId?: string
}

withDefaults(defineProps<Props>(), {
  awb: 'PHA-260104',
  batchId: '#MCV-2026-X12',
  commodityTitle: 'Vaksin Meningococcal Konjugat ACYW',
  packageCountStr: '2 Koli Insulated Box',
  originFacility: 'PT BioFarma (Bandung)',
  destinationFacility: 'RS Medika Sehat Jakarta',
  tempRangeStr: '2.0°C – 8.0°C',
  inspectorName: 'Hendra Wijaya',
  inspectorId: 'CHK-012',
})
</script>

<template>
  <section class="relative overflow-hidden rounded-xl bg-surface-container-lowest p-6 shadow-sm mb-6 border border-surface-container/60">
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
      <div class="flex flex-col gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <span class="bg-primary text-on-primary font-label-md text-label-md px-3 py-1 rounded-md tracking-wider font-bold shadow-2xs">
            AWB: {{ awb }}
          </span>
          <span class="bg-surface-container text-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-md font-mono font-bold">
            BATCH {{ batchId }}
          </span>
          <span class="bg-secondary-container text-on-secondary-container font-label-sm text-label-sm px-2.5 py-1 rounded-md flex items-center gap-1 font-bold shadow-2xs">
            <span class="material-symbols-outlined text-xs">verified</span>
            FRD-02 REGULATED
          </span>
        </div>

        <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-bold">
          {{ commodityTitle }}
        </h1>

        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 font-body-sm text-body-sm text-on-surface-variant">
          <span class="flex items-center gap-1.5 font-semibold text-on-surface">
            <span class="material-symbols-outlined text-sm text-primary">inventory_2</span>
            {{ packageCountStr }}
          </span>
          <span class="text-outline-variant">•</span>
          <span class="flex items-center gap-1">
            <span class="material-symbols-outlined text-sm text-secondary">domain</span>
            {{ originFacility }}
          </span>
          <span class="text-outline-variant">➔</span>
          <span class="flex items-center gap-1">
            <span class="material-symbols-outlined text-sm text-primary">local_hospital</span>
            {{ destinationFacility }}
          </span>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end gap-3 shrink-0">
        <!-- Cold Chain Mandatory Range Badge -->
        <div class="bg-surface-container-low px-4 py-2.5 rounded-lg flex items-center gap-3 border border-surface-container/60 shadow-2xs">
          <div class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0 shadow-2xs">
            <span class="material-symbols-outlined text-sm">ac_unit</span>
          </div>
          <div>
            <span class="font-label-sm text-label-sm uppercase text-on-surface-variant block tracking-wider font-medium">Batas Suhu Wajib CPOB</span>
            <span class="font-headline-sm text-headline-sm text-secondary font-bold">{{ tempRangeStr }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 font-label-sm text-label-sm text-on-surface-variant">
          <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
          <span>Tahap: Initial Exam Lapangan • Inspector {{ inspectorName }} ({{ inspectorId }})</span>
        </div>
      </div>
    </div>
  </section>
</template>

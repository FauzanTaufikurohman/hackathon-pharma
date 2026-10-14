<script setup lang="ts">
interface Props {
  gateName?: string
  hubStagingArea?: string
  calibrationStatus?: string
}

withDefaults(defineProps<Props>(), {
  gateName: 'Gate Inspeksi #02',
  hubStagingArea: 'Hub Cawang Staging Cold Room',
  calibrationStatus: 'Validasi Logger: Terkalibrasi Aktif',
})
</script>

<template>
  <div class="relative overflow-hidden rounded-xl bg-surface-container-low shadow-sm p-5 mb-8 border border-surface-container/60">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
      <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-lg bg-surface-container-lowest flex items-center justify-center text-primary shadow-sm border border-surface-container/40 shrink-0">
          <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">
            ac_unit
          </span>
        </div>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary bg-secondary-fixed/50 px-2 py-0.5 rounded font-bold shadow-2xs">
              BPOM CDOB COMPLIANT
            </span>
            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
            <span class="font-label-md text-label-md text-on-surface-variant font-semibold">
              {{ gateName }}
            </span>
          </div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface mt-0.5 font-bold">
            Terminal Pemeriksaan Awal Gudang (Warehouse Initial Exam) • {{ hubStagingArea }}
          </h2>
        </div>
      </div>

      <div class="flex items-center gap-2 self-start md:self-auto bg-surface-container-lowest px-3 py-1.5 rounded-lg shadow-sm border border-surface-container/40">
        <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
        <span class="font-label-md text-label-md text-on-surface font-semibold">
          {{ calibrationStatus }}
        </span>
      </div>
    </div>
  </div>
</template>

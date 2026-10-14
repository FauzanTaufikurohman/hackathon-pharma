<script setup lang="ts">
const emit = defineEmits<{
  (e: 'printSheet'): void
  (e: 'downloadLogPdf'): void
}>()

function handlePrint() {
  emit('printSheet')
  window.print()
}
</script>

<template>
  <div class="mt-space-lg p-space-md rounded-xl bg-surface-container-low flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-md border border-surface-container">
    <div class="flex items-center gap-space-md">
      <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-[20px]">inventory_2</span>
      </div>
      <div>
        <div class="font-label-lg text-label-lg text-on-surface font-bold">
          Persiapan Serah Terima Faskes RS Medika Sehat
        </div>
        <div class="font-body-sm text-[13px] text-on-surface-variant mt-0.5 leading-relaxed">
          Pastikan PIC Farmasi standby dengan alat ukur kalibrasi pembanding dan berita acara penerimaan dingin.
        </div>
      </div>
    </div>

    <div class="flex items-center gap-space-sm self-end sm:self-auto shrink-0 flex-wrap">
      <button
        type="button"
        @click="handlePrint"
        class="px-3.5 py-2 bg-surface-container-lowest hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-bold rounded-lg shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer border border-surface-container-high active:scale-95"
      >
        <span class="material-symbols-outlined text-[16px]">print</span>
        <span>Cetak Lembar Lacak</span>
      </button>

      <button
        type="button"
        @click="emit('downloadLogPdf')"
        class="px-3.5 py-2 bg-secondary text-on-secondary hover:bg-on-secondary-container font-label-md text-label-md font-bold rounded-lg shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer active:scale-95"
      >
        <span class="material-symbols-outlined text-[16px]">download</span>
        <span>Unduh Data Log Suhu (.PDF)</span>
      </button>
    </div>
  </div>
</template>

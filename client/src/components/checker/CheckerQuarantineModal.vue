<script setup lang="ts">
import type { CheckerHistoryItem } from '../../types/shipment'

interface Props {
  isOpen: boolean
  item: CheckerHistoryItem | null
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'download-bap'): void
}>()
</script>

<template>
  <div
    v-if="isOpen && item"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"
  >
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-error/30">
      <!-- Modal Header -->
      <div class="p-5 border-b border-error/20 flex items-center justify-between bg-error-container/30">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg bg-error text-white flex items-center justify-center shadow-xs">
            <span class="material-symbols-outlined text-xl">assignment_late</span>
          </div>
          <div>
            <h3 class="font-headline-sm text-headline-sm text-error font-bold">
              Berita Acara Karantina &amp; Penolakan Mutu Farmasi (BAP-CDOB)
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant font-mono">
              No. BAP: BAP-KRN-2026-095 • Resi: {{ item.awb }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
        <!-- Alert Status Box -->
        <div class="p-4 bg-error-container/30 rounded-xl border border-error/30 flex items-start gap-3">
          <span class="material-symbols-outlined text-error text-2xl mt-0.5">report_problem</span>
          <div>
            <h4 class="font-headline-sm text-headline-sm text-error font-bold">
              Kargo Ditahan: Melanggar Standar Integritas Rantai Dingin CDOB
            </h4>
            <p class="font-body-sm text-body-sm text-on-surface mt-1">
              Barang telah diisolasi secara fisik ke dalam <strong>Ruang Karantina Gudang Staging #02</strong> dan dilarang diserahterimakan kepada kurir pengantar.
            </p>
          </div>
        </div>

        <!-- Violation Details -->
        <div class="grid grid-cols-2 gap-3">
          <div class="p-3.5 bg-surface-container-low rounded-xl border border-surface-container">
            <span class="font-label-sm text-label-sm text-on-surface-variant block mb-1">Penyebab 1: Kerusakan Segel</span>
            <div class="flex items-center gap-1.5 text-error font-bold font-label-md">
              <span class="material-symbols-outlined text-base">broken_image</span>
              <span>Segel Keamanan Rusak / Terbuka</span>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
              Terdapat indikasi akses tanpa otorisasi pada box isolasi sekunder.
            </p>
          </div>

          <div class="p-3.5 bg-surface-container-low rounded-xl border border-surface-container">
            <span class="font-label-sm text-label-sm text-on-surface-variant block mb-1">Penyebab 2: Ekskursi Termal</span>
            <div class="flex items-center gap-1.5 text-error font-bold font-label-md">
              <span class="material-symbols-outlined text-base">thermostat</span>
              <span>Suhu Tercatat: 8.9°C</span>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
              Deviasi +0.9°C melebihi batas batas kritis farmasi 2.0°C – 8.0°C.
            </p>
          </div>
        </div>

        <!-- Official Investigation Order -->
        <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container space-y-2">
          <div class="flex items-center justify-between">
            <span class="font-label-sm text-label-sm text-error font-bold uppercase tracking-wider">
              Instruksi Investigasi QA Dispatcher:
            </span>
            <span class="font-body-sm text-body-sm text-on-surface-variant font-mono">13 Okt 2026, 16:22 WIB</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface leading-relaxed">
            "Segel fisik terbukti robek dan suhu tercatat 8.9°C melanggar standar CPOB BPOM. Pindahkan kargo segera ke Ruang Karantina Gudang Staging #02 untuk investigasi vendor BioFarma."
          </p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-between">
        <button
          type="button"
          @click="emit('download-bap')"
          class="px-4 py-2 rounded-xl bg-surface-container-lowest hover:bg-surface-container border border-surface-container text-on-surface font-label-md text-label-md font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
        >
          <span class="material-symbols-outlined text-sm text-error">print</span>
          <span>Cetak BAP Resmi</span>
        </button>

        <button
          type="button"
          @click="emit('close')"
          class="px-5 py-2.5 rounded-xl bg-error text-white font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs hover:opacity-90"
        >
          Tutup Berita Acara
        </button>
      </div>
    </div>
  </div>
</template>

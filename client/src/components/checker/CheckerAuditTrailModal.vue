<script setup lang="ts">
import type { CheckerHistoryItem } from '../../types/shipment'

interface Props {
  isOpen: boolean
  item: CheckerHistoryItem | null
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div
    v-if="isOpen && item"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"
  >
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-surface-container">
      <!-- Modal Header -->
      <div class="p-5 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg bg-secondary text-white flex items-center justify-center shadow-xs">
            <span class="material-symbols-outlined text-xl">receipt_long</span>
          </div>
          <div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
              Audit Trail Kriptografis CDOB (Immutable Log)
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant font-mono">
              AWB: {{ item.awb }} • Batch Closed
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
        <!-- Digital Hash Badge -->
        <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container flex items-center justify-between">
          <div>
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold block mb-0.5">
              Blockchain Ledger Hash ID
            </span>
            <span class="font-mono text-xs text-primary font-bold break-all">
              0x89f41b2c7e098d651a23e590fb78663cd48102a9b3487f5d9472e391
            </span>
          </div>
          <span class="px-2.5 py-1 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs">
            <span class="material-symbols-outlined text-xs">verified</span>
            Tervalidasi
          </span>
        </div>

        <!-- Timeline Log Items -->
        <div class="space-y-3 relative pl-6 border-l-2 border-surface-container-high ml-3">
          <!-- Step 1 -->
          <div class="relative">
            <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-secondary ring-4 ring-surface-container-lowest"></span>
            <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container">
              <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface font-bold">Initial Exam Diserahkan</span>
                <span class="font-body-sm text-body-sm text-on-surface-variant font-mono">13 Okt, 14:00 WIB</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                Pemeriksaan fisik oleh Checker Hendra Wijaya (CHK-012). Seluruh 3 koli obat tablet farmasi rutin dinyatakan utuh.
              </p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="relative">
            <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-secondary ring-4 ring-surface-container-lowest"></span>
            <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container">
              <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface font-bold">Persetujuan QA Dispatcher</span>
                <span class="font-body-sm text-body-sm text-on-surface-variant font-mono">13 Okt, 14:05 WIB</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                Admin Control Tower menyetujui rilis kargo untuk pengiriman ambient menuju RSUP Fatmawati.
              </p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="relative">
            <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-secondary ring-4 ring-surface-container-lowest"></span>
            <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container">
              <div class="flex items-center justify-between">
                <span class="font-label-md text-label-md text-on-surface font-bold">e-POD Serah Terima Tuntas</span>
                <span class="font-body-sm text-body-sm text-on-surface-variant font-mono">13 Okt, 14:10 WIB</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                Kurir telah menyelesaikan serah terima (e-POD tuntas dengan tanda tangan digital Apt. Siti Rahma di RSUP Fatmawati).
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-between">
        <span class="font-body-sm text-body-sm text-on-surface-variant">
          Standar Integritas CDOB BPOM 2024
        </span>
        <button
          type="button"
          @click="emit('close')"
          class="px-5 py-2.5 rounded-xl bg-surface-container-highest hover:bg-surface-container text-on-surface font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs"
        >
          Tutup Audit Trail
        </button>
      </div>
    </div>
  </div>
</template>

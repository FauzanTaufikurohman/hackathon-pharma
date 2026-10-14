<script setup lang="ts">
import { ref } from 'vue'
import type { BapReport } from '../../types/shipment'
import { mockBapReport } from '../../data/mockShipments'

interface Props {
  bap?: BapReport
  trackingNumber?: string
}

const props = withDefaults(defineProps<Props>(), {
  bap: () => mockBapReport,
  trackingNumber: 'PHA-260104',
})

const emit = defineEmits<{
  (e: 'download-bap', filename: string): void
  (e: 'share-telegram', chatId: string): void
  (e: 'print-bap'): void
}>()

// Interactive state for download BAP button
const isDownloading = ref(false)
const downloadDownloaded = ref(false)

// Interactive state for telegram share button
const isTelegramSent = ref(false)

function handleDownloadBap() {
  if (isDownloading.value || downloadDownloaded.value) return
  isDownloading.value = true

  setTimeout(() => {
    isDownloading.value = false
    downloadDownloaded.value = true
    emit('download-bap', `BAP-${props.trackingNumber}-Final.pdf`)

    setTimeout(() => {
      downloadDownloaded.value = false
    }, 2500)
  }, 1200)
}

function handleTelegramShare() {
  if (isTelegramSent.value) return
  isTelegramSent.value = true
  emit('share-telegram', '@rsmedika_farmasi')

  setTimeout(() => {
    isTelegramSent.value = false
  }, 2500)
}

function handlePrint() {
  emit('print-bap')
  window.print()
}
</script>

<template>
  <section class="rounded-xl bg-surface-container-lowest p-6 shadow-sm border border-surface-container relative overflow-hidden">
    <!-- Header: Title & CPOB Validated Badge -->
    <div class="flex items-center justify-between pb-4 border-b border-surface-container-low mb-4">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>
        <h2 class="font-headline-md text-headline-md font-bold text-on-surface">
          Berita Acara Penerimaan (BAP)
        </h2>
      </div>
      <span class="px-3 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm uppercase font-bold shrink-0">
        CPOB Validated
      </span>
    </div>

    <p class="font-body-sm text-body-sm text-on-surface-variant mb-5">
      Rekapitulasi resmi sesuai tabel relasi <code class="font-mono bg-surface-container-low px-1.5 py-0.5 rounded text-primary">reports</code> dan <code class="font-mono bg-surface-container-low px-1.5 py-0.5 rounded text-primary">shipments</code>.
    </p>

    <!-- Metadata Spec Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
      <!-- Item 1: Tracking Number -->
      <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container/60">
        <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5 font-bold">
          No. Resi (tracking_number)
        </div>
        <div class="font-headline-sm text-headline-sm font-bold text-on-surface">
          {{ trackingNumber }}
        </div>
      </div>

      <!-- Item 2: Final Status -->
      <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container/60">
        <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5 font-bold">
          Status Akhir Pengiriman
        </div>
        <div class="font-headline-sm text-headline-sm font-bold text-secondary flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-secondary"></span>
          {{ bap.status_label }}
        </div>
      </div>

      <!-- Item 3: Temperature Range (Span 2) -->
      <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container/60 sm:col-span-2">
        <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5 font-bold">
          Rentang Suhu Kargo (temperature_min – max)
        </div>
        <div class="flex items-baseline gap-2">
          <span class="font-headline-md text-headline-md font-bold text-secondary">
            {{ bap.temperature_min }}°C – {{ bap.temperature_max }}°C
          </span>
          <span class="font-label-md text-label-md text-on-surface-variant font-medium">
            {{ bap.safe_profile_label }}
          </span>
        </div>

        <!-- Telemetry Mini Bar -->
        <div class="mt-2.5 w-full bg-surface-container-highest rounded-full h-2 relative">
          <div class="absolute left-[20%] right-[30%] h-2 bg-secondary rounded-full"></div>
        </div>
        <div class="flex justify-between text-[10px] text-on-surface-variant font-mono mt-1">
          <span>0.0°C (Batas Bawah)</span>
          <span class="text-secondary font-bold">Safe Cold-Chain 2°C - 8°C</span>
          <span>12.0°C (Batas Atas)</span>
        </div>
      </div>

      <!-- Item 4: Revision Version -->
      <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container/60">
        <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5 font-bold">
          Versi Dokumen (revision)
        </div>
        <div class="font-body-md text-body-md font-semibold text-on-surface">
          {{ bap.revision_label }}
        </div>
      </div>

      <!-- Item 5: Recipient Officer -->
      <div class="p-3.5 rounded-xl bg-surface-container-low border border-surface-container/60">
        <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5 font-bold">
          Petugas Penerima Resmi
        </div>
        <div class="font-body-md text-body-md font-semibold text-on-surface">
          {{ bap.recipient_officer }}
        </div>
        <div class="font-label-sm text-label-sm text-on-surface-variant">
          {{ bap.recipient_nipa }}
        </div>
      </div>
    </div>

    <!-- Electronic Signature Verification Badge -->
    <div class="p-4 rounded-xl bg-surface-container flex items-start gap-3 mb-6 border border-surface-container-high">
      <div class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-[18px]">draw</span>
      </div>
      <div>
        <div class="font-label-lg text-label-lg font-bold text-on-surface flex items-center gap-1.5">
          <span>Tanda Tangan Elektronik APJ Terverifikasi</span>
          <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
          Berita Acara Serah Terima sah secara hukum per Permenkes &amp; pedoman BPOM Indonesia untuk distribusi biologis beku.
        </p>
      </div>
    </div>

    <!-- Action Buttons (Anteraja Magenta Palette) -->
    <div class="flex flex-col gap-3">
      <!-- Download Button with Loading & Success States -->
      <button
        type="button"
        id="btn-download-bap"
        @click="handleDownloadBap"
        :disabled="isDownloading"
        class="w-full h-12 rounded-lg bg-primary-container text-on-primary font-label-lg text-label-lg font-semibold flex items-center justify-center gap-2 hover:bg-primary transition-all shadow-sm active:scale-[0.99] cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
      >
        <template v-if="isDownloading">
          <span class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
          <span>Menyiapkan Berkas PDF...</span>
        </template>
        <template v-else-if="downloadDownloaded">
          <span class="material-symbols-outlined text-[20px]">check</span>
          <span>BAP-{{ trackingNumber }}-Final.pdf Terunduh</span>
        </template>
        <template v-else>
          <span class="material-symbols-outlined text-[20px]">download</span>
          <span>Unduh Laporan &amp; BAP Resmi (PDF)</span>
        </template>
      </button>

      <!-- Telegram Notification Share Button -->
      <button
        type="button"
        id="btn-telegram-share"
        @click="handleTelegramShare"
        class="w-full h-11 rounded-lg bg-surface-container-lowest text-on-surface font-label-lg text-label-lg font-semibold flex items-center justify-center gap-2 hover:bg-surface-container-low transition-all shadow-sm border border-surface-container cursor-pointer"
      >
        <template v-if="isTelegramSent">
          <span class="material-symbols-outlined text-secondary text-[20px]">check_circle</span>
          <span class="text-secondary font-bold">Notifikasi Terkirim ke @rsmedika_farmasi</span>
        </template>
        <template v-else>
          <span class="material-symbols-outlined text-secondary text-[20px]">send</span>
          <span>Kirim Salinan ke Telegram (@rsmedika_farmasi)</span>
        </template>
      </button>
    </div>

    <!-- Print Link -->
    <div class="mt-4 text-center">
      <button
        type="button"
        @click="handlePrint"
        class="inline-flex items-center gap-1.5 text-primary hover:underline font-label-sm text-label-sm font-semibold cursor-pointer"
      >
        <span class="material-symbols-outlined text-[16px]">print</span>
        <span>Cetak Salinan Fisik untuk Arsip BPOM</span>
      </button>
    </div>
  </section>
</template>

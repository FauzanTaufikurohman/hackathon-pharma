<script setup lang="ts">
interface Props {
  bapNumber?: string
  pdfSize?: string
  dispatcherPhone?: string
}

withDefaults(defineProps<Props>(), {
  bapNumber: 'BAP-PHA-20261014-8891',
  pdfSize: '1.8 MB',
  dispatcherPhone: '081288889999',
})

const emit = defineEmits<{
  (e: 'download-pdf'): void
  (e: 'share-whatsapp'): void
}>()
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm space-y-5 border border-surface-container/60">
    <div class="flex items-start gap-4">
      <div class="w-12 h-12 rounded-xl bg-error-container text-on-error-container flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-2xl">picture_as_pdf</span>
      </div>
      <div class="min-w-0 flex-1">
        <span class="font-label-sm text-label-sm text-primary uppercase font-bold block">
          Dokumen Resmi Terbit
        </span>
        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold leading-snug mt-0.5">
          Berita Acara Penerimaan (BAP) &amp; Laporan Audit Rantai Dingin
        </h3>
        <p class="font-mono text-xs text-on-surface-variant mt-1">
          ID: {{ bapNumber }} • PDF CDOB BPOM Validated
        </p>
      </div>
    </div>

    <!-- Security & Encryption Badge -->
    <div class="p-3 rounded-lg bg-surface-container-low flex items-center justify-between text-xs text-on-surface-variant border border-surface-container/40">
      <span class="flex items-center gap-1.5 font-medium">
        <span class="material-symbols-outlined text-base text-secondary">security</span>
        <span>Enkripsi Digital SHA-256 Otentik</span>
      </span>
      <span class="font-mono font-semibold">{{ pdfSize }}</span>
    </div>

    <!-- Action Buttons -->
    <div class="space-y-3 pt-1">
      <!-- Primary Action Button: Download PDF -->
      <button
        type="button"
        @click="emit('download-pdf')"
        class="w-full bg-primary-container hover:bg-primary text-on-primary font-label-lg text-label-lg font-bold py-3 px-6 rounded-xl flex items-center justify-center gap-2 shadow-sm transition-colors cursor-pointer active:scale-95"
      >
        <span class="material-symbols-outlined text-xl">download</span>
        <span>Unduh Salinan Laporan Tugas Kurir (PDF)</span>
      </button>

      <!-- Secondary Action Button: WhatsApp Dispatcher -->
      <button
        type="button"
        @click="emit('share-whatsapp')"
        class="w-full bg-surface-container-low hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors cursor-pointer border border-surface-container/40 active:scale-95"
      >
        <span class="material-symbols-outlined text-secondary text-lg">chat</span>
        <span>Kirim Ringkasan Laporan ke WhatsApp Dispatcher</span>
      </button>
    </div>
  </div>
</template>

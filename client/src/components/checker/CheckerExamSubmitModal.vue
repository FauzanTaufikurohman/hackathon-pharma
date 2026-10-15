<script setup lang="ts">
import { useRouter } from 'vue-router'

interface Props {
  isOpen: boolean
  isPassed: boolean
  awb: string
  temperature: number | string
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const router = useRouter()

const goToHistory = () => {
  emit('close')
  router.push('/checker/riwayat')
}

const goToQueue = () => {
  emit('close')
  router.push('/checker')
}
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"
  >
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-surface-container">
      <!-- Icon & Title Header -->
      <div class="p-6 text-center bg-surface-container-low border-b border-surface-container flex flex-col items-center">
        <div
          class="w-16 h-16 rounded-full flex items-center justify-center mb-3 shadow-md"
          :class="isPassed ? 'bg-secondary-container text-secondary' : 'bg-error-container text-error'"
        >
          <span class="material-symbols-outlined text-4xl">
            {{ isPassed ? 'check_circle' : 'gpp_bad' }}
          </span>
        </div>

        <h3 class="font-headline-md text-headline-md text-on-surface font-bold">
          {{ isPassed ? 'Initial Exam Berhasil Disubmit!' : 'Kargo Ditandai Gagal / Ditahan' }}
        </h3>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
          Tiket inspeksi AWB <strong class="text-on-surface font-mono">{{ awb }}</strong> telah dikirim ke Admin QA Control Tower.
        </p>
      </div>

      <!-- Detail list -->
      <div class="p-6 space-y-3">
        <div class="p-3.5 bg-surface-container-low rounded-xl border border-surface-container space-y-2">
          <div class="flex items-center justify-between text-sm">
            <span class="text-on-surface-variant font-medium">Status Pengiriman:</span>
            <span
              class="font-bold font-mono px-2 py-0.5 rounded text-xs"
              :class="isPassed ? 'bg-secondary-container text-secondary' : 'bg-error-container text-error'"
            >
              {{ isPassed ? 'pending_approval' : 'held / rejected' }}
            </span>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-on-surface-variant font-medium">Pembacaan Suhu:</span>
            <span class="text-on-surface font-bold font-mono">{{ temperature }}°C</span>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-on-surface-variant font-medium">Pemeriksa QC:</span>
            <span class="text-on-surface font-medium">Hendra Wijaya (CHK-012)</span>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-on-surface-variant font-medium">Audit Trail Hash:</span>
            <span class="text-primary font-mono text-xs font-bold">0x89A2...CDOB</span>
          </div>
        </div>

        <p class="font-body-sm text-body-sm text-on-surface-variant text-center">
          Admin Dispatcher akan meninjau kargo ini sebelum mengalokasikan armada kurir reefer van.
        </p>
      </div>

      <!-- Action buttons -->
      <div class="p-5 bg-surface-container-low border-t border-surface-container flex flex-col sm:flex-row gap-3">
        <button
          type="button"
          @click="goToHistory"
          class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs hover:bg-primary-container text-center flex items-center justify-center gap-1.5"
        >
          <span>Pantau Status di Riwayat</span>
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </button>

        <button
          type="button"
          @click="goToQueue"
          class="px-4 py-2.5 rounded-xl bg-surface-container-highest hover:bg-surface-container text-on-surface font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs text-center"
        >
          Kembali ke Antrean
        </button>
      </div>
    </div>
  </div>
</template>

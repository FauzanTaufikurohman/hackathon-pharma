<script setup lang="ts">
interface Props {
  isProcessing: boolean
  isAccepted: boolean
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'reject'): void
  (e: 'accept'): void
}>()
</script>

<template>
  <div class="p-6 md:p-8 bg-surface-container-low flex flex-col-reverse sm:flex-row items-center justify-between gap-4 border-t border-surface-container">
    <!-- Left Compliance Note -->
    <div class="flex items-center gap-2 text-on-surface-variant">
      <span class="material-symbols-outlined text-sm">shield</span>
      <span class="font-label-sm text-label-sm font-mono uppercase font-semibold">
        Kepatuhan Good Distribution Practice (BPOM 2026)
      </span>
    </div>

    <!-- Right Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
      <!-- Reject Button -->
      <button
        v-if="!isAccepted"
        type="button"
        @click="emit('reject')"
        class="w-full sm:w-auto px-6 py-4 rounded-xl bg-error-container text-on-error-container hover:bg-error-container/80 font-label-lg text-label-lg transition-all flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] cursor-pointer font-semibold"
      >
        <span class="material-symbols-outlined text-lg">close</span>
        <span>Tolak Tugas (Input Alasan Penolakan)</span>
      </button>

      <!-- Accept Button -->
      <button
        type="button"
        :disabled="isProcessing || isAccepted"
        @click="emit('accept')"
        class="w-full sm:w-auto px-8 py-4 rounded-xl font-headline-sm text-headline-sm font-bold shadow-md transition-all flex items-center justify-center gap-3 active:scale-[0.98] cursor-pointer"
        :class="{
          'bg-primary-container text-on-primary-container hover:bg-primary': !isProcessing && !isAccepted,
          'bg-primary-container text-on-primary-container opacity-90 cursor-wait': isProcessing,
          'bg-secondary text-white cursor-default': isAccepted
        }"
      >
        <template v-if="isProcessing">
          <span class="material-symbols-outlined animate-spin text-xl">sync</span>
          <span>Memproses Server...</span>
        </template>
        <template v-else-if="isAccepted">
          <span class="material-symbols-outlined text-xl">check_circle</span>
          <span>Tugas Diterima</span>
        </template>
        <template v-else>
          <span class="material-symbols-outlined text-xl">task_alt</span>
          <span>Terima Tugas Pengantaran</span>
        </template>
      </button>
    </div>
  </div>
</template>

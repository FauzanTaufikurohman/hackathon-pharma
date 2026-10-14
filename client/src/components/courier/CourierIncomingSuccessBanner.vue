<script setup lang="ts">
interface Props {
  awb: string
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'navigate-monitor'): void
}>()
</script>

<template>
  <div
    class="w-full bg-secondary-fixed text-on-secondary-fixed p-6 rounded-xl shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-secondary/30 animate-in fade-in slide-in-from-top-4 duration-300"
  >
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-full bg-secondary flex items-center justify-center text-white shrink-0 shadow-xs">
        <span class="material-symbols-outlined text-2xl">check</span>
      </div>
      <div>
        <h4 class="font-headline-sm text-headline-sm font-bold text-on-secondary-fixed">
          Tugas Berhasil Diterima! (Status: Accepted)
        </h4>
        <p class="font-body-md text-body-md text-on-secondary-fixed mt-0.5">
          Data tersinkron ke Pengantaran &amp; Monitor. Silakan ambil paket di Staging Room #02 dan lakukan scan barcode.
        </p>
      </div>
    </div>

    <button
      type="button"
      @click="emit('navigate-monitor')"
      class="px-6 py-3 rounded-xl bg-secondary text-white font-label-lg text-label-lg font-bold shadow hover:bg-on-secondary-container transition-all shrink-0 cursor-pointer active:scale-95 flex items-center gap-2"
    >
      <span>Mulai Navigasi &amp; Monitor</span>
      <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
    </button>
  </div>
</template>

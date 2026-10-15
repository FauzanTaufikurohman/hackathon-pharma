<script setup lang="ts">
interface Props {
  isOpen: boolean
  photoUrl: string
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-fade-in"
  >
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden border border-surface-container">
      <div class="p-4 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-xl">zoom_in</span>
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">
            Inspeksi Bukti Foto Resolusi Tinggi
          </span>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <div class="relative bg-black flex items-center justify-center p-2">
        <img
          :src="photoUrl"
          alt="Foto Detail Pemeriksaan Cold Box"
          class="max-h-[70vh] w-auto object-contain rounded-lg shadow-2xl"
        />

        <!-- Watermark info in modal -->
        <div class="absolute bottom-4 left-4 bg-black/70 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs font-mono">
          Probe Digital: 4.2°C • Segel: SL-8891 • Geotag: Hub Cawang Staging
        </div>
      </div>

      <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-end">
        <button
          type="button"
          @click="emit('close')"
          class="px-5 py-2 rounded-xl bg-primary text-white font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs hover:bg-primary-container"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  isOpen: boolean
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'scanned', awb: string): void
}>()

const manualInput = ref('')

const handleSelectAwb = (awb: string) => {
  emit('scanned', awb)
  emit('close')
}

const handleManualSubmit = () => {
  if (manualInput.value.trim()) {
    emit('scanned', manualInput.value.trim().toUpperCase())
    manualInput.value = ''
    emit('close')
  }
}
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"
  >
    <div
      class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-surface-container"
    >
      <!-- Header -->
      <div class="p-5 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-xs">
            <span class="material-symbols-outlined text-xl">qr_code_scanner</span>
          </div>
          <div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
              Scanner Barcode &amp; QR Code AWB
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Arahkan ke label kemasan cold-box atau thermal packaging
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

      <!-- Camera Viewfinder Mockup -->
      <div class="p-6">
        <div class="relative w-full aspect-4/3 bg-slate-950 rounded-xl overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-slate-700 shadow-inner">
          <!-- Corner reticles -->
          <div class="absolute top-4 left-4 w-6 h-6 border-t-2 border-l-2 border-primary-container"></div>
          <div class="absolute top-4 right-4 w-6 h-6 border-t-2 border-r-2 border-primary-container"></div>
          <div class="absolute bottom-4 left-4 w-6 h-6 border-b-2 border-l-2 border-primary-container"></div>
          <div class="absolute bottom-4 right-4 w-6 h-6 border-b-2 border-r-2 border-primary-container"></div>

          <!-- Laser Scan Line -->
          <div class="absolute left-8 right-8 h-0.5 bg-gradient-to-r from-transparent via-primary-container to-transparent shadow-[0_0_12px_#d81e5b] animate-bounce"></div>

          <!-- Center Camera Indicator -->
          <span class="material-symbols-outlined text-4xl text-slate-600 mb-2">barcode_scanner</span>
          <span class="font-label-md text-label-md text-slate-400">Pindai kode 2D DataMatrix / Barcode 128</span>
          <span class="font-body-sm text-body-sm text-emerald-400 font-mono mt-1 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Kamera Staging Aktif (Sensor 60 FPS)
          </span>
        </div>

        <!-- Quick Scan Buttons for Prototype Demo -->
        <div class="mt-4">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold tracking-wider block mb-2">
            Simulasi Cepat Barcode Staging Hub:
          </span>
          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="handleSelectAwb('PHA-260104')"
              class="px-2.5 py-2 rounded-lg bg-surface-container-low hover:bg-primary-fixed hover:text-primary transition-colors text-center border border-surface-container font-mono font-bold text-xs cursor-pointer shadow-2xs"
            >
              PHA-260104
              <span class="block font-sans text-[10px] text-on-surface-variant font-normal">Vaksin Meningo</span>
            </button>
            <button
              type="button"
              @click="handleSelectAwb('PHA-260099')"
              class="px-2.5 py-2 rounded-lg bg-surface-container-low hover:bg-tertiary-fixed hover:text-on-tertiary-fixed transition-colors text-center border border-surface-container font-mono font-bold text-xs cursor-pointer shadow-2xs"
            >
              PHA-260099
              <span class="block font-sans text-[10px] text-on-surface-variant font-normal">Insulin Pen</span>
            </button>
            <button
              type="button"
              @click="handleSelectAwb('PHA-260108')"
              class="px-2.5 py-2 rounded-lg bg-surface-container-low hover:bg-secondary-fixed hover:text-on-secondary-fixed-variant transition-colors text-center border border-surface-container font-mono font-bold text-xs cursor-pointer shadow-2xs"
            >
              PHA-260108
              <span class="block font-sans text-[10px] text-on-surface-variant font-normal">Serum Anti-Bisa</span>
            </button>
          </div>
        </div>

        <!-- Manual Input Alternative -->
        <div class="mt-4 pt-3 border-t border-surface-container/60">
          <form @submit.prevent="handleManualSubmit" class="flex gap-2">
            <input
              v-model="manualInput"
              type="text"
              placeholder="Atau ketik No. Resi manual..."
              class="flex-1 bg-surface-container-low text-on-surface placeholder:text-on-surface-variant px-3.5 py-2.5 rounded-xl font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest border border-surface-container focus:border-primary-container"
            />
            <button
              type="submit"
              class="bg-surface-container-high hover:bg-primary hover:text-white px-4 py-2.5 rounded-xl font-label-md text-label-md text-on-surface transition-colors font-bold cursor-pointer shadow-2xs"
            >
              Cari
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

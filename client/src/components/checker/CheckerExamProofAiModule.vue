<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  temperature: number | string
  packagingPassed: boolean
  sealPassed: boolean
  labelPassed: boolean
  photoUrl?: string
  ocrTemp?: string
  ocrSeal?: string
}

const props = withDefaults(defineProps<Props>(), {
  photoUrl: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB5WXkJBEZndlRE6Vn9T2vwslOCmIzkK2Y3A9RyttgfzuJMPgDS19zmjMUpn1qFNPL7j-kjdzOWvI1AO38PuyXyIypXn1D8GQxuysS2cGqj449Ad5_5-p-ZWXx_WOUtRZq7spSbSj-5iWzNCq3CWE0-JfV40JAI2EiaN03b61M2VTsGU4P4Q6m6rBx4-cn4F4xQtzjX9cAwds6xeLjB1bfnOsWOMeYII_e9IiUKPcyAotY8eGXf6Ttnsg',
  ocrTemp: '4.2',
  ocrSeal: 'SL-8891',
})

const emit = defineEmits<{
  (e: 'retake-photo'): void
  (e: 'zoom-photo'): void
  (e: 'submit-exam', passed: boolean): void
  (e: 'reject-exam'): void
}>()

// Temperature valid in CPOB range 2.0 - 8.0
const isTempValid = computed(() => {
  const t = Number(props.temperature)
  return !isNaN(t) && t >= 2.0 && t <= 8.0
})

// Auto calculated gate result
const isOverallPassed = computed(() => {
  return props.packagingPassed && props.sealPassed && props.labelPassed && isTempValid.value
})

// OCR match check
const isOcrMatch = computed(() => {
  return Number(props.temperature) === Number(props.ocrTemp)
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col gap-5 border border-surface-container/60">
    <!-- Header -->
    <div class="flex items-center justify-between pb-1 border-b border-surface-container/40">
      <div>
        <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider block font-bold">
          Kamera Lapangan &amp; AI
        </span>
        <h2 class="font-headline-md text-headline-md text-on-surface font-bold">
          2. Bukti Fisik &amp; AI Vision
        </h2>
      </div>
      <span class="bg-primary-fixed text-primary font-label-sm text-label-sm px-2.5 py-1 rounded-md font-bold shadow-2xs">
        FR-02
      </span>
    </div>

    <!-- Photo Preview Container -->
    <div class="relative rounded-xl overflow-hidden group shadow-sm bg-surface-container-highest border border-surface-container/60">
      <img
        class="w-full h-56 object-cover transition-transform duration-300 group-hover:scale-102"
        alt="Foto Pemeriksaan Cold Box Staging Hub Cawang"
        :src="photoUrl"
      />
      <!-- Watermark Overlay Badge -->
      <div class="absolute bottom-2 left-2 right-2 bg-inverse-surface/85 backdrop-blur-sm text-inverse-on-surface px-3 py-2 rounded-lg flex items-center justify-between text-xs shadow-lg">
        <span class="font-label-sm text-label-sm flex items-center gap-1 font-medium">
          <span class="material-symbols-outlined text-xs text-primary-fixed-dim">location_on</span>
          Hub Cawang Staging • 14 Okt 2026 09:42 WIB
        </span>
        <span class="font-label-sm text-label-sm text-secondary-fixed flex items-center gap-0.5 font-bold">
          <span class="material-symbols-outlined text-xs">gps_fixed</span>
          GPS Locked
        </span>
      </div>
    </div>

    <!-- Photo Actions -->
    <div class="grid grid-cols-2 gap-3">
      <button
        type="button"
        @click="emit('retake-photo')"
        class="bg-surface-container text-on-surface hover:bg-surface-container-high font-label-md text-label-md py-2.5 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-all cursor-pointer font-semibold shadow-2xs active:scale-98"
      >
        <span class="material-symbols-outlined text-sm">photo_camera</span>
        <span>Ambil Foto Ulang</span>
      </button>

      <button
        type="button"
        @click="emit('zoom-photo')"
        class="bg-surface-container text-on-surface hover:bg-surface-container-high font-label-md text-label-md py-2.5 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-all cursor-pointer font-semibold shadow-2xs active:scale-98"
      >
        <span class="material-symbols-outlined text-sm">zoom_in</span>
        <span>Perbesar Foto</span>
      </button>
    </div>

    <!-- AI Vision OCR Verification Card (Core Differentiator) -->
    <div class="relative overflow-hidden rounded-xl bg-surface-container-low p-4 flex flex-col gap-3 border border-surface-container/60 shadow-inner">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center shadow-2xs">
            <span class="material-symbols-outlined text-xs text-white">auto_awesome</span>
          </div>
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">Hasil Analisis AI Vision OCR</span>
        </div>
        <span class="font-label-sm text-label-sm bg-primary-fixed text-primary px-2 py-0.5 rounded font-bold shadow-2xs">
          v3.2 Edge
        </span>
      </div>

      <!-- Comparison Grid -->
      <div class="flex flex-col gap-2 pt-1 font-body-sm text-body-sm">
        <div class="flex items-center justify-between py-1.5 bg-surface-container-lowest px-3 rounded-lg border border-surface-container/40">
          <span class="text-on-surface-variant font-label-md text-label-md font-medium">Suhu Input Manual Checker</span>
          <span class="font-headline-sm text-headline-sm text-on-surface font-bold">{{ temperature }}°C</span>
        </div>

        <div class="flex items-center justify-between py-1.5 bg-surface-container-lowest px-3 rounded-lg border border-surface-container/40">
          <span class="text-on-surface-variant font-label-md text-label-md font-medium">Suhu Terdeteksi AI OCR (Display)</span>
          <span class="font-headline-sm text-headline-sm text-secondary font-bold">{{ ocrTemp }}°C</span>
        </div>

        <div class="flex items-center justify-between py-1.5 bg-surface-container-lowest px-3 rounded-lg border border-surface-container/40">
          <span class="text-on-surface-variant font-label-md text-label-md font-medium">Status Validasi OCR</span>
          <span
            v-if="isOcrMatch"
            class="bg-secondary-container text-on-secondary-container font-label-sm text-label-sm px-2.5 py-1 rounded-md flex items-center gap-1 font-bold shadow-2xs"
          >
            <span class="material-symbols-outlined text-xs">check</span>
            AI Vision Match (100% Sesuai)
          </span>
          <span
            v-else
            class="bg-amber-100 text-tertiary font-label-sm text-label-sm px-2.5 py-1 rounded-md flex items-center gap-1 font-bold shadow-2xs"
          >
            <span class="material-symbols-outlined text-xs">warning</span>
            Perbedaan Input vs OCR
          </span>
        </div>

        <div class="flex items-center justify-between py-1.5 bg-surface-container-lowest px-3 rounded-lg border border-surface-container/40">
          <span class="text-on-surface-variant font-label-md text-label-md font-medium">Deteksi Segel Fisik</span>
          <span class="font-label-sm text-label-sm text-on-surface font-semibold flex items-center gap-1">
            <span class="material-symbols-outlined text-xs text-secondary">verified</span>
            <span class="font-mono font-bold">{{ ocrSeal }}</span> (Integritas Utuh)
          </span>
        </div>
      </div>
    </div>

    <!-- Final Submission & Approval Gate Box -->
    <div class="pt-2 flex flex-col gap-3">
      <!-- inspections.passed indicator -->
      <div
        class="p-4 rounded-xl text-center shadow-sm transition-all"
        :class="isOverallPassed ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-error'"
      >
        <span class="font-label-sm text-label-sm tracking-wider uppercase block opacity-90 font-medium">
          Auto-Calculated Gate Result
        </span>
        <div class="font-headline-md text-headline-md font-bold mt-0.5 flex items-center justify-center gap-1.5">
          <span class="material-symbols-outlined text-xl">
            {{ isOverallPassed ? 'task_alt' : 'cancel' }}
          </span>
          <span>
            STATUS: {{ isOverallPassed ? 'LOLOS UJI (PASSED = TRUE)' : 'TIDAK LOLOS (PASSED = FALSE)' }}
          </span>
        </div>
        <p class="font-body-sm text-body-sm opacity-90 mt-1">
          {{ isOverallPassed ? 'Memenuhi seluruh kriteria CDOB BPOM RI & SOP Cold-Chain' : 'Terdapat deviasi fisik/suhu yang melanggar standar CPOB BPOM RI' }}
        </p>
      </div>

      <!-- Primary Submit Button -->
      <button
        type="button"
        @click="emit('submit-exam', isOverallPassed)"
        class="bg-primary text-on-primary hover:bg-primary-container font-label-lg text-label-lg py-3.5 px-6 rounded-xl w-full shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer font-bold active:scale-98"
      >
        <span class="material-symbols-outlined text-xl">send</span>
        <span>Submit Hasil Pemeriksaan ke Admin (pending_approval)</span>
      </button>

      <!-- Secondary Danger Action Button -->
      <button
        type="button"
        @click="emit('reject-exam')"
        class="bg-surface-container-high text-error hover:bg-error-container hover:text-on-error-container font-label-md text-label-md py-2.5 px-6 rounded-xl w-full transition-all flex items-center justify-center gap-1.5 cursor-pointer font-bold active:scale-98"
      >
        <span class="material-symbols-outlined text-base">report</span>
        <span>Tandai Gagal / Tolak Barang (passed = false &amp; status -&gt; held)</span>
      </button>
    </div>
  </div>
</template>

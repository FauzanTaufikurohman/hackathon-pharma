<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  check1: boolean
  check2: boolean
  check3: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:check1', val: boolean): void
  (e: 'update:check2', val: boolean): void
  (e: 'update:check3', val: boolean): void
}>()

const completedCount = computed(() => {
  return [props.check1, props.check2, props.check3].filter(Boolean).length
})

const isAllChecked = computed(() => completedCount.value === 3)
</script>

<template>
  <div class="p-6 md:p-8 bg-surface-container-lowest border-t border-surface-container/60">
    <!-- Header Block -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
      <div>
        <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 font-bold">
          <span class="material-symbols-outlined text-secondary text-[22px]">fact_check</span>
          Daftar Verifikasi Pra-Jalan Satria Pharma (Pre-Trip Checklist)
        </h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Selesaikan seluruh poin verifikasi sebelum melakukan konfirmasi tugas di sistem.
        </p>
      </div>

      <!-- Counter Pill -->
      <span
        class="font-label-md text-label-md px-3 py-1 font-mono rounded-full font-bold self-start md:self-auto transition-colors"
        :class="isAllChecked
          ? 'bg-secondary-fixed text-on-secondary-fixed shadow-2xs'
          : 'bg-surface-container text-on-surface'"
      >
        {{ completedCount }} / 3 Terverifikasi
      </span>
    </div>

    <!-- 3-Column Checklist Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Check 1 -->
      <label
        class="group relative flex items-start gap-3.5 p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all cursor-pointer shadow-xs border"
        :class="check1 ? 'border-secondary/40 bg-secondary-fixed/10' : 'border-surface-container/60'"
      >
        <input
          type="checkbox"
          :checked="check1"
          @change="emit('update:check1', ($event.target as HTMLInputElement).checked)"
          class="mt-0.5 w-5 h-5 rounded text-primary-container focus:ring-primary-container accent-primary cursor-pointer shrink-0"
        />
        <div class="flex flex-col">
          <span class="font-label-lg text-label-lg text-on-surface group-hover:text-primary transition-colors font-semibold">
            1. Segel &amp; Kunci Fisik Box
          </span>
          <span class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Active Cooler Box terpasang kokoh &amp; tersegel SL-8891 utuh tanpa retak.
          </span>
        </div>
      </label>

      <!-- Check 2 -->
      <label
        class="group relative flex items-start gap-3.5 p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all cursor-pointer shadow-xs border"
        :class="check2 ? 'border-secondary/40 bg-secondary-fixed/10' : 'border-surface-container/60'"
      >
        <input
          type="checkbox"
          :checked="check2"
          @change="emit('update:check2', ($event.target as HTMLInputElement).checked)"
          class="mt-0.5 w-5 h-5 rounded text-primary-container focus:ring-primary-container accent-primary cursor-pointer shrink-0"
        />
        <div class="flex flex-col">
          <span class="font-label-lg text-label-lg text-on-surface group-hover:text-primary transition-colors font-semibold">
            2. Icepack PCM Dingin Optimal
          </span>
          <span class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            PCM conditioning stabil, suhu internal chamber terbaca 4.2°C di display box.
          </span>
        </div>
      </label>

      <!-- Check 3 -->
      <label
        class="group relative flex items-start gap-3.5 p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all cursor-pointer shadow-xs border"
        :class="check3 ? 'border-secondary/40 bg-secondary-fixed/10' : 'border-surface-container/60'"
      >
        <input
          type="checkbox"
          :checked="check3"
          @change="emit('update:check3', ($event.target as HTMLInputElement).checked)"
          class="mt-0.5 w-5 h-5 rounded text-primary-container focus:ring-primary-container accent-primary cursor-pointer shrink-0"
        />
        <div class="flex flex-col">
          <span class="font-label-lg text-label-lg text-on-surface group-hover:text-primary transition-colors font-semibold">
            3. Telemetri IoT Sensirion Aktif
          </span>
          <span class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Beacon Bluetooth &amp; GSM logger terhubung, GPS transmisi interval 30 detik.
          </span>
        </div>
      </label>
    </div>
  </div>
</template>

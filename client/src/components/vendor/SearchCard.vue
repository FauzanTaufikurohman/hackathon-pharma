<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  initialAwb?: string
}>()

const emit = defineEmits<{
  (e: 'track', awb: string): void
  (e: 'selectChip', awb: string, itemName: string): void
}>()

const searchQuery = ref(props.initialAwb || 'PHA-260104')

function onTrackSubmit() {
  if (searchQuery.value.trim()) {
    emit('track', searchQuery.value.trim())
  }
}

function handleChipClick(awb: string, itemName: string) {
  searchQuery.value = awb
  emit('selectChip', awb, itemName)
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container p-space-lg flex flex-col gap-space-md relative overflow-hidden">
    <!-- Subtle Ambient Gradient -->
    <div class="absolute right-0 top-0 w-80 h-80 bg-gradient-to-bl from-primary-fixed/20 via-transparent to-transparent pointer-events-none rounded-full blur-2xl -mr-16 -mt-16"></div>

    <!-- Header Section -->
    <div class="flex flex-col gap-1 relative z-10">
      <label class="font-label-lg text-label-lg text-on-surface font-bold flex items-center gap-2" for="awbSearchInput">
        <span class="material-symbols-outlined text-primary text-[20px]">qr_code_scanner</span>
        Cari Nomor Resi / Muatan Kargo Dingin
      </label>
      <span class="font-body-sm text-body-sm text-outline">
        Masukkan nomor AWB atau nama vaksin/biologis untuk membuka monitor telemetri langsung.
      </span>
    </div>

    <!-- Search Form -->
    <form @submit.prevent="onTrackSubmit" class="flex flex-col sm:flex-row items-stretch gap-space-sm relative z-10">
      <div class="relative flex-1">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[20px] pointer-events-none">search</span>
        <input
          id="awbSearchInput"
          v-model="searchQuery"
          type="text"
          placeholder="Contoh: PHA-260104 (Vaksin Meningococcal)..."
          class="w-full h-12 pl-11 pr-4 bg-surface-container-low border border-surface-container-high rounded-xl font-body-md text-body-md text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary transition-all"
        />
      </div>
      <button
        type="submit"
        class="h-12 px-6 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md font-semibold rounded-xl shadow-xs flex items-center justify-center gap-2 transition-all cursor-pointer whitespace-nowrap active:scale-[0.98]"
      >
        <span class="material-symbols-outlined text-[20px]">monitor_heart</span>
        <span>Lacak &amp; Buka Monitor Pengiriman</span>
      </button>
    </form>

    <!-- Quick-chip tags: Kargo Terjadwal -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-2 pt-1 text-body-sm font-body-sm relative z-10">
      <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider shrink-0 font-bold">Kargo Terjadwal:</span>
      <div class="flex flex-wrap items-center gap-2">
        <!-- Chip 1: PHA-260104 -->
        <button
          type="button"
          @click="handleChipClick('PHA-260104', 'Vaksin Meningococcal')"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-fixed/50 hover:bg-primary-fixed text-primary font-label-md text-label-md transition-colors text-left cursor-pointer border border-primary/20 shadow-2xs"
        >
          <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
          <span class="font-bold">PHA-260104</span>
          <span class="text-on-surface-variant font-normal">&bull; Vaksin Meningococcal</span>
          <span class="px-1.5 py-0.5 rounded text-[10px] bg-tertiary-fixed text-on-tertiary-fixed font-bold uppercase ml-1">
            Menunggu Persetujuan
          </span>
        </button>

        <!-- Chip 2: PHA-260105 -->
        <button
          type="button"
          @click="handleChipClick('PHA-260105', 'Serum Anti-Bisa Ular')"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant font-label-md text-label-md transition-colors cursor-pointer border border-surface-container-high shadow-2xs"
        >
          <span class="w-2 h-2 rounded-full bg-outline"></span>
          <span class="font-bold text-on-surface">PHA-260105</span>
          <span>&bull; Serum Anti-Bisa Ular</span>
          <span class="px-1.5 py-0.5 rounded text-[10px] bg-surface-variant text-on-surface font-bold uppercase ml-1">
            Dalam Perjalanan
          </span>
        </button>

        <!-- Chip 3: PHA-260098 -->
        <button
          type="button"
          @click="handleChipClick('PHA-260098', 'Vaksin Influenza Flubio')"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant font-label-md text-label-md transition-colors cursor-pointer border border-surface-container-high shadow-2xs"
        >
          <span class="w-2 h-2 rounded-full bg-secondary"></span>
          <span class="font-bold text-on-surface">PHA-260098</span>
          <span>&bull; Vaksin Influenza Flubio</span>
          <span class="px-1.5 py-0.5 rounded text-[10px] bg-secondary-fixed text-on-secondary-fixed font-bold uppercase ml-1">
            Selesai
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

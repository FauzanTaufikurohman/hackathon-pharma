<script setup lang="ts">
interface Props {
  searchQuery: string
  activeFilter: string
  totalCount?: number
  vaccineCount?: number
  recheckCount?: number
}

withDefaults(defineProps<Props>(), {
  totalCount: 3,
  vaccineCount: 2,
  recheckCount: 1,
})

const emit = defineEmits<{
  (e: 'update:searchQuery', val: string): void
  (e: 'filter-change', filter: string): void
  (e: 'open-scanner'): void
}>()
</script>

<template>
  <div class="bg-surface-container-lowest rounded-2xl shadow-sm p-4 mb-6 border border-surface-container/60">
    <!-- Top Search & Barcode Trigger Row -->
    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
      <div class="relative flex-1">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">
          qr_code_scanner
        </span>
        <input
          :value="searchQuery"
          @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
          class="w-full bg-surface-container-low text-on-surface placeholder:text-on-surface-variant pl-12 pr-4 py-3 rounded-xl font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest shadow-inner border border-transparent focus:border-primary-container transition-all"
          placeholder="Cari atau Scan QR No. Resi PHA-260XXX atau Batch ID..."
          type="text"
        />
      </div>

      <button
        type="button"
        @click="emit('open-scanner')"
        class="bg-primary-container hover:bg-primary text-on-primary font-label-lg text-label-lg px-6 py-3.5 rounded-xl shadow-md flex items-center justify-center gap-2 transition-all active:scale-95 whitespace-nowrap cursor-pointer font-bold"
      >
        <span class="material-symbols-outlined text-xl">barcode_scanner</span>
        <span>Scan Barcode / QR AWB</span>
      </button>
    </div>

    <!-- Filter Pills Bar -->
    <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-surface-container/40">
      <!-- Pill 1: Semua -->
      <button
        type="button"
        @click="emit('filter-change', 'all')"
        class="font-label-md text-label-md px-4 py-1.5 rounded-full transition-all cursor-pointer font-semibold"
        :class="activeFilter === 'all'
          ? 'bg-primary text-on-primary shadow-xs font-bold'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        Semua Antrean ({{ totalCount }})
      </button>

      <!-- Pill 2: Vaksin & Biologis -->
      <button
        type="button"
        @click="emit('filter-change', 'vaccine')"
        class="font-label-md text-label-md px-4 py-1.5 rounded-full transition-all cursor-pointer font-semibold"
        :class="activeFilter === 'vaccine'
          ? 'bg-primary text-on-primary shadow-xs font-bold'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        Vaksin &amp; Biologis ({{ vaccineCount }})
      </button>

      <!-- Pill 3: Recheck Request -->
      <button
        type="button"
        @click="emit('filter-change', 'recheck')"
        class="font-label-md text-label-md px-4 py-1.5 rounded-full transition-all cursor-pointer flex items-center gap-1.5 font-semibold"
        :class="activeFilter === 'recheck'
          ? 'bg-primary text-on-primary shadow-xs font-bold'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        <span class="w-2 h-2 rounded-full bg-tertiary"></span>
        <span>Recheck Request ({{ recheckCount }})</span>
      </button>
    </div>
  </div>
</template>

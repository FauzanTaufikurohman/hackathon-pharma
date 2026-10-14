<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  searchQuery: string
  activeFilter: string
  counts: {
    all: number
    assigned: number
    in_transit: number
    hold: number
    delivered: number
  }
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:searchQuery', query: string): void
  (e: 'update:activeFilter', filter: string): void
}>()
</script>

<template>
  <!-- Search & Filter Controls -->
  <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-xs border border-surface-container/60 mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <!-- Search Bar -->
    <div class="relative w-full lg:w-96">
      <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px] pointer-events-none">
        search
      </span>
      <input
        id="manifest-search-input"
        :value="searchQuery"
        @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-surface-container-low font-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary-container/20 transition-all border border-transparent focus:border-outline-variant"
        placeholder="Cari No. Resi PHA-260XXX atau Fasilitas Kesehatan..."
        type="text"
      />
    </div>

    <!-- Segment Filter Pills -->
    <div class="flex flex-wrap items-center gap-2" id="filter-pills-group">
      <!-- Pill 1: Semua -->
      <button
        type="button"
        @click="emit('update:activeFilter', 'all')"
        class="filter-pill font-label-md px-3.5 py-2 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer font-semibold"
        :class="activeFilter === 'all'
          ? 'bg-primary-fixed text-primary-container font-bold shadow-xs ring-2 ring-primary-container/20'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
      >
        <span>Semua</span>
        <span class="px-1.5 py-0.5 rounded-full bg-surface-variant text-[10px] font-bold">
          {{ counts.all }}
        </span>
      </button>

      <!-- Pill 2: Perlu Konfirmasi (assigned) -->
      <button
        type="button"
        @click="emit('update:activeFilter', 'assigned')"
        class="filter-pill font-label-md px-3.5 py-2 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer font-semibold"
        :class="activeFilter === 'assigned'
          ? 'bg-primary-fixed text-primary-container font-bold shadow-xs ring-2 ring-primary-container/20'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
      >
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-container opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-container"></span>
        </span>
        <span>Perlu Konfirmasi</span>
        <span
          class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
          :class="activeFilter === 'assigned'
            ? 'bg-primary-container text-on-primary'
            : 'bg-primary-container/20 text-primary-container'"
        >
          {{ counts.assigned }}
        </span>
      </button>

      <!-- Pill 3: Dalam Perjalanan (in_transit) -->
      <button
        type="button"
        @click="emit('update:activeFilter', 'in_transit')"
        class="filter-pill font-label-md px-3.5 py-2 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer font-semibold"
        :class="activeFilter === 'in_transit'
          ? 'bg-primary-fixed text-primary-container font-bold shadow-xs ring-2 ring-primary-container/20'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
      >
        <span>Dalam Perjalanan</span>
        <span class="px-1.5 py-0.5 rounded-full bg-tertiary-fixed text-tertiary font-bold text-[10px]">
          {{ counts.in_transit }}
        </span>
      </button>

      <!-- Pill 4: Ditahan / Kendala (hold) -->
      <button
        type="button"
        @click="emit('update:activeFilter', 'hold')"
        class="filter-pill font-label-md px-3.5 py-2 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer font-semibold"
        :class="activeFilter === 'hold'
          ? 'bg-primary-fixed text-primary-container font-bold shadow-xs ring-2 ring-primary-container/20'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-surface-container opacity-70'"
      >
        <span>Ditahan / Kendala</span>
        <span class="px-1.5 py-0.5 rounded-full bg-surface-variant text-[10px] font-bold">
          {{ counts.hold }}
        </span>
      </button>

      <!-- Pill 5: Selesai (delivered) -->
      <button
        type="button"
        @click="emit('update:activeFilter', 'delivered')"
        class="filter-pill font-label-md px-3.5 py-2 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer font-semibold"
        :class="activeFilter === 'delivered'
          ? 'bg-primary-fixed text-primary-container font-bold shadow-xs ring-2 ring-primary-container/20'
          : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-surface-container'"
      >
        <span>Selesai</span>
        <span class="px-1.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-bold text-[10px]">
          {{ counts.delivered }}
        </span>
      </button>
    </div>
  </div>
</template>

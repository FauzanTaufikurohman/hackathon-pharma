<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  activeFilter: string
  counts: {
    all: number
    transit: number
    qc: number
    delivered: number
  }
}>()

const emit = defineEmits<{
  (e: 'update:activeFilter', filter: string): void
  (e: 'search', query: string): void
}>()

const searchQuery = ref('')

function onSearchInput() {
  emit('search', searchQuery.value)
}

function selectFilter(filter: string) {
  emit('update:activeFilter', filter)
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-xs border border-slate-200/80 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
    <!-- Search Input -->
    <div class="relative flex-1">
      <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">
        search
      </span>
      <input
        v-model="searchQuery"
        @input="onSearchInput"
        type="text"
        placeholder="Cari nomor resi, nama obat, atau tujuan..."
        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-800 placeholder:text-slate-400 font-body-md text-body-md outline-none focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all border border-slate-200 focus:border-primary"
      />
    </div>

    <!-- Filter Pills -->
    <div class="flex flex-wrap items-center gap-2">
      <!-- Semua -->
      <button
        type="button"
        @click="selectFilter('all')"
        class="px-4 py-2 rounded-xl font-label-md text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="activeFilter === 'all'
          ? 'bg-primary text-white shadow-xs'
          : 'bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-pink-300 hover:bg-pink-50/40'"
      >
        Semua ({{ counts.all }})
      </button>

      <!-- Dalam Perjalanan -->
      <button
        type="button"
        @click="selectFilter('transit')"
        class="px-4 py-2 rounded-xl font-label-md text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="activeFilter === 'transit'
          ? 'bg-primary text-white shadow-xs'
          : 'bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-pink-300 hover:bg-pink-50/40'"
      >
        Dalam Perjalanan ({{ counts.transit }})
      </button>

      <!-- Pemeriksaan QC -->
      <button
        type="button"
        @click="selectFilter('qc')"
        class="px-4 py-2 rounded-xl font-label-md text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="activeFilter === 'qc'
          ? 'bg-amber-600 text-white shadow-xs'
          : 'bg-white border border-slate-200 text-slate-600 hover:text-amber-700 hover:border-amber-300 hover:bg-amber-50/40'"
      >
        Pemeriksaan QC ({{ counts.qc }})
      </button>

      <!-- Tiba Hari Ini -->
      <button
        type="button"
        @click="selectFilter('delivered')"
        class="px-4 py-2 rounded-xl font-label-md text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="activeFilter === 'delivered'
          ? 'bg-indigo-600 text-white shadow-xs'
          : 'bg-white border border-slate-200 text-slate-600 hover:text-indigo-700 hover:border-indigo-300 hover:bg-indigo-50/40'"
      >
        Tiba Hari Ini ({{ counts.delivered }})
      </button>
    </div>
  </div>
</template>

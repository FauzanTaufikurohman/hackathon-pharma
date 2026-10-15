<script setup lang="ts">
export type HistoryFilterType = 'all' | 'pending' | 'approved' | 'rejected'

interface Props {
  searchQuery: string
  activeFilter: HistoryFilterType
  allCount?: number
  pendingCount?: number
  approvedCount?: number
  rejectedCount?: number
}

withDefaults(defineProps<Props>(), {
  allCount: 16,
  pendingCount: 2,
  approvedCount: 13,
  rejectedCount: 1,
})

const emit = defineEmits<{
  (e: 'update:searchQuery', val: string): void
  (e: 'filter-change', val: HistoryFilterType): void
}>()
</script>

<template>
  <div class="bg-surface-container-lowest p-4 rounded-xl shadow-sm space-y-4 mb-8 border border-surface-container/60">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <!-- Segmented Category Filters -->
      <div class="flex flex-wrap items-center gap-2" id="filter-group">
        <!-- Filter 1: Semua Riwayat -->
        <button
          type="button"
          @click="emit('filter-change', 'all')"
          class="px-4 py-2 rounded-full font-label-md text-label-md transition-all flex items-center gap-2 cursor-pointer font-semibold"
          :class="activeFilter === 'all'
            ? 'bg-surface-container-highest text-on-surface shadow-sm font-bold'
            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'"
        >
          <span>Semua Riwayat</span>
          <span class="px-1.5 py-0.5 rounded-full bg-surface-container-highest text-on-surface text-label-sm font-label-sm font-bold">
            {{ allCount }}
          </span>
        </button>

        <!-- Filter 2: Menunggu Persetujuan Admin -->
        <button
          type="button"
          @click="emit('filter-change', 'pending')"
          class="px-4 py-2 rounded-full font-label-md text-label-md transition-all flex items-center gap-2 cursor-pointer font-semibold"
          :class="activeFilter === 'pending'
            ? 'bg-surface-container-highest text-on-surface shadow-sm font-bold'
            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'"
        >
          <span class="w-2 h-2 rounded-full bg-tertiary-fixed-dim"></span>
          <span>Menunggu Persetujuan Admin</span>
          <span class="px-1.5 py-0.5 rounded-full bg-amber-100 text-tertiary text-label-sm font-label-sm font-bold">
            {{ pendingCount }}
          </span>
        </button>

        <!-- Filter 3: Disetujui Admin -->
        <button
          type="button"
          @click="emit('filter-change', 'approved')"
          class="px-4 py-2 rounded-full font-label-md text-label-md transition-all flex items-center gap-2 cursor-pointer font-semibold"
          :class="activeFilter === 'approved'
            ? 'bg-surface-container-highest text-on-surface shadow-sm font-bold'
            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'"
        >
          <span class="w-2 h-2 rounded-full bg-secondary"></span>
          <span>Disetujui Admin</span>
          <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-secondary text-label-sm font-label-sm font-bold">
            {{ approvedCount }}
          </span>
        </button>

        <!-- Filter 4: Ditahan / Ditolak -->
        <button
          type="button"
          @click="emit('filter-change', 'rejected')"
          class="px-4 py-2 rounded-full font-label-md text-label-md transition-all flex items-center gap-2 cursor-pointer font-semibold"
          :class="activeFilter === 'rejected'
            ? 'bg-surface-container-highest text-on-surface shadow-sm font-bold'
            : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'"
        >
          <span class="w-2 h-2 rounded-full bg-error"></span>
          <span>Ditahan / Ditolak</span>
          <span class="px-1.5 py-0.5 rounded-full bg-error-container text-error text-label-sm font-label-sm font-bold">
            {{ rejectedCount }}
          </span>
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative w-full lg:w-80">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">
          search
        </span>
        <input
          :value="searchQuery"
          @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
          class="w-full pl-9 pr-4 py-2 h-10 rounded-lg bg-surface-container-low font-body-sm text-body-sm text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary/20 border border-transparent focus:border-primary-container transition-all"
          placeholder="Cari No. Resi, Komoditas, atau Tanggal..."
          type="text"
        />
      </div>
    </div>
  </div>
</template>

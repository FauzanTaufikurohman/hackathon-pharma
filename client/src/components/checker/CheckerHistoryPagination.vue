<script setup lang="ts">
interface Props {
  currentPage?: number
  displayedCount?: number
  totalCount?: number
}

withDefaults(defineProps<Props>(), {
  currentPage: 1,
  displayedCount: 4,
  totalCount: 16,
})

const emit = defineEmits<{
  (e: 'page-change', page: number): void
}>()
</script>

<template>
  <div class="mt-8 pt-6 pb-2 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-surface-container/40">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-lg bg-secondary-container/40 flex items-center justify-center text-secondary shadow-2xs">
        <span class="material-symbols-outlined text-xl">verified_user</span>
      </div>
      <div>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Menampilkan <strong class="text-on-surface">{{ displayedCount }}</strong> dari <strong class="text-on-surface">{{ totalCount }}</strong> pemeriksaan tercatat • Standar Integritas CDOB BPOM 2024
        </p>
        <span class="font-label-sm text-label-sm text-on-surface-variant/80 uppercase tracking-widest font-bold">
          Sertifikasi Audit Trail ISO 9001:2015 / GDP Pharma Logistics
        </span>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center gap-2">
      <button
        type="button"
        @click="emit('page-change', currentPage - 1)"
        :disabled="currentPage <= 1"
        class="px-3 py-1.5 rounded-lg bg-surface-container-lowest text-on-surface-variant font-label-md text-label-md hover:bg-surface-container-high transition-colors disabled:opacity-40 disabled:cursor-not-allowed border border-surface-container cursor-pointer font-semibold shadow-2xs"
      >
        Sebelumnya
      </button>

      <div class="flex items-center gap-1">
        <button
          v-for="p in [1, 2, 3]"
          :key="p"
          type="button"
          @click="emit('page-change', p)"
          class="w-8 h-8 rounded-lg font-label-md text-label-md flex items-center justify-center transition-all cursor-pointer font-bold shadow-2xs"
          :class="currentPage === p
            ? 'bg-primary text-on-primary shadow-xs'
            : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container border border-surface-container'"
        >
          {{ p }}
        </button>
      </div>

      <button
        type="button"
        @click="emit('page-change', currentPage + 1)"
        :disabled="currentPage >= 3"
        class="px-3 py-1.5 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-surface-container-high transition-colors font-label-md text-label-md border border-surface-container cursor-pointer font-semibold shadow-2xs disabled:opacity-40 disabled:cursor-not-allowed"
      >
        Selanjutnya
      </button>
    </div>
  </div>
</template>

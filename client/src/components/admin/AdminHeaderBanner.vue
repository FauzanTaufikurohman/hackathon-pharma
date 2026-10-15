<script setup lang="ts">
const emit = defineEmits<{
  (e: 'export-csv'): void
}>()
</script>

<template>
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-on-surface">
        Manajemen pengiriman
      </h1>
      <p class="mt-1 text-sm text-on-surface-variant">
        Daftarkan pengiriman dan pantau status pemeriksaan serta perjalanannya.
      </p>
    </div>

    <div class="flex shrink-0 items-center gap-2">
      <button
        type="button"
        @click="emit('export-csv')"
        class="flex items-center gap-2 rounded-lg border border-surface-container px-3.5 py-2.5 text-sm font-semibold text-on-surface transition hover:bg-surface-container-low"
      >
        <span class="material-symbols-outlined text-base">download</span>
        Ekspor CSV
      </button>
    </div>
  </div>
</template>

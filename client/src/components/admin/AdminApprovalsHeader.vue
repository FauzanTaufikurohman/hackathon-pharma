<script setup lang="ts">
interface Props {
  queueCount?: number
}

withDefaults(defineProps<Props>(), {
  queueCount: 2,
})
</script>

<template>
  <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-on-surface">
        Persetujuan pemeriksaan &amp; penugasan kurir
      </h1>
      <p class="mt-1 text-sm text-on-surface-variant">
        Tinjau hasil pemeriksaan. Pengiriman yang disetujui dapat ditugaskan ke kurir.
      </p>
    </div>

    <div class="shrink-0 rounded-lg bg-white px-3 py-2 text-sm text-slate-600">
      <span class="font-semibold text-slate-900">{{ queueCount }}</span> pengiriman menunggu
    </div>
  </div>
</template>

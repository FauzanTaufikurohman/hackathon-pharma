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
      <div class="flex items-center gap-2 mb-1 flex-wrap">
        <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary uppercase tracking-widest bg-primary-fixed/60 px-2.5 py-0.5 rounded-full font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
          GERBANG KONTROL KEPATUHAN &amp; DISPATCH KURIR • CDOB BPOM REGULATED
        </span>
        <span class="font-label-sm text-label-sm text-on-surface-variant font-semibold">• FR-02 &amp; FR-03 GATEKEEPER</span>
      </div>
      <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-bold">
        Persetujuan QC Gudang &amp; Penugasan Kurir Satria
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-3xl">
        Tinjau bukti fisik hasil Initial Exam dari Checker QC, berikan otorisasi kelayakan jalan, dan delegasikan kargo ke armada kurir berpendingin.
      </p>
    </div>

    <!-- Quick Stats Indicator -->
    <div class="flex items-center gap-3 shrink-0">
      <div class="bg-surface-container-lowest shadow-sm rounded-xl px-4 py-2 flex items-center gap-3 border border-surface-container-low">
        <div class="w-2.5 h-2.5 rounded-full bg-tertiary-container animate-pulse"></div>
        <div class="flex flex-col">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Antrean Otorisasi</span>
          <span class="font-label-lg text-label-lg text-on-surface font-bold">{{ queueCount }} Kargo Menunggu</span>
        </div>
      </div>
      <div class="bg-surface-container-lowest shadow-sm rounded-xl px-4 py-2 flex items-center gap-3 border border-surface-container-low">
        <span class="material-symbols-outlined text-secondary text-xl">verified_user</span>
        <div class="flex flex-col">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Standar GDP</span>
          <span class="font-label-lg text-label-lg text-secondary font-bold">BPOM CPOB 2026</span>
        </div>
      </div>
    </div>
  </div>
</template>

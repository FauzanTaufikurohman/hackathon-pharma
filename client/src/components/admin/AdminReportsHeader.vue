<script setup lang="ts">
// AdminReportsHeader.vue
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md mb-8">
    <div class="space-y-1.5 max-w-3xl">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm uppercase tracking-wider">
          <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
          Audit Trail Mutlak • Log Notifikasi • Generator BAP
        </span>
        <span class="text-on-surface-variant font-label-sm text-label-sm hidden sm:inline">
          • Kepatuhan CDOB / ISO 9001 / ISO 27001
        </span>
      </div>
      <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight">
        Laporan Kepatuhan CDOB &amp; Log Audit Terpadu
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
        Arsip log audit mutlak (immutable cryptographic events), pemantauan broadcast notifikasi bot multi-kanal, dan generator Berita Acara Penerimaan (BAP) berstandar Cara Distribusi Obat yang Baik.
      </p>
    </div>

    <!-- Right: Ledger Integrity Badge -->
    <div class="flex items-center gap-3 self-start md:self-end shrink-0">
      <div class="bg-surface-container-low px-4 py-2.5 rounded-xl flex items-center gap-3 border border-surface-container shadow-2xs">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">
          verified_user
        </span>
        <div class="flex flex-col">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
            Integritas Ledger
          </span>
          <span class="font-label-md text-label-md text-secondary font-bold">
            SHA-256 Validated
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

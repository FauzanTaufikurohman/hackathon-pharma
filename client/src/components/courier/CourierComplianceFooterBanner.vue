<script setup lang="ts">
</script>

<template>
  <!-- Bottom Regulatory & Dispatch Compliance Strip -->
  <div class="mt-8 p-5 bg-surface-container-lowest rounded-2xl shadow-xs flex flex-col md:flex-row items-center justify-between gap-4 border border-surface-container/60">
    <div class="flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-secondary-container flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-on-secondary-container text-[22px]">verified</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-md font-bold text-on-surface">
          Kepatuhan Cara Distribusi Obat yang Baik (CDOB) &amp; ISO 9001:2015
        </span>
        <span class="font-body-sm text-on-surface-variant">
          Penyimpangan suhu &gt;8.0°C wajib dilaporkan dalam kurun waktu 15 menit melalui modul Excursion Alerting Satria.
        </span>
      </div>
    </div>

    <div class="flex items-center gap-3 shrink-0">
      <span class="font-label-sm uppercase tracking-wide text-on-surface-variant font-semibold">
        Status Jaringan Telemetri:
      </span>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm font-bold shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
        IoT Gateway Aktif
      </span>
    </div>
  </div>
</template>

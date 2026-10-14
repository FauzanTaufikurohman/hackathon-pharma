<script setup lang="ts">
// Sub-header & Navigation Context
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md mb-8">
    <div class="space-y-1">
      <div class="flex items-center gap-space-xs text-outline font-label-md text-label-md">
        <router-link to="/paket-saya" class="hover:text-primary transition-colors cursor-pointer">
          Portal Buyer / Faskes
        </router-link>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-primary font-bold">Monitor Pengiriman</span>
      </div>
      <h1 class="font-display-lg text-[28px] sm:text-[32px] text-on-surface font-extrabold tracking-tight">
        Status Kesiapan &amp; Pemeriksaan Awal Kargo
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
        Pantau verifikasi fisik, suhu cold-chain awal, dan konfirmasi penerimaan fasilitas farmasi Anda.
      </p>
    </div>

    <!-- Live Telemetry Status Pill -->
    <div class="flex items-center gap-3 bg-surface-container-lowest px-4 py-2.5 rounded-full shadow-xs border border-surface-container self-start md:self-auto shrink-0">
      <span class="relative flex h-2.5 w-2.5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary"></span>
      </span>
      <div class="font-label-sm text-[11px] text-on-surface uppercase tracking-wider font-semibold">
        Sistem IoT Aktif <span class="text-secondary font-bold ml-1">&bull; Transmisi 30s</span>
      </div>
    </div>
  </div>
</template>

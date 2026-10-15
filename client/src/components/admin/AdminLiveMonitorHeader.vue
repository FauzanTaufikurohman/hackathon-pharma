<script setup lang="ts">
// AdminLiveMonitorHeader component
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-2 text-primary font-label-sm text-label-sm uppercase tracking-wider font-bold">
        <span class="inline-block w-2 h-2 rounded-full bg-primary animate-pulse"></span>
        MONITORING TELEMETRI IOT &amp; RESOLUSI KENDALA LAPANGAN • 24/7 CONTROL TOWER
      </div>
      <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight font-bold">
        Live Tracking Armada, Telemetri Suhu &amp; Resolusi Kendala
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl">
        Pantau posisi armada kurir secara real-time, pantau pita suhu sensor IoT kargo dingin, dan eksekusi keputusan resmi terhadap insiden lapangan.
      </p>
    </div>

    <!-- Telemetry Status Right Box -->
    <div class="flex items-center gap-2 self-start md:self-auto bg-surface-container-low px-4 py-2 rounded-xl border border-surface-container shrink-0 shadow-2xs">
      <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">sensors</span>
      <div class="flex flex-col">
        <span class="font-label-sm text-label-sm text-on-surface font-bold">Data Telemetri MQTT Aktif</span>
        <span class="font-body-sm text-body-sm text-on-surface-variant">Update: 15 detik lalu (Sync 100%)</span>
      </div>
    </div>
  </div>
</template>

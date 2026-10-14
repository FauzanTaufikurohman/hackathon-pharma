<script setup lang="ts">
interface Props {
  currentTemp?: number
  probeId?: string
  batteryPct?: number
  protocol?: string
  pingCycle?: string
}

withDefaults(defineProps<Props>(), {
  currentTemp: 4.5,
  probeId: 'SENS-IOT-771',
  batteryPct: 96,
  protocol: 'BLE 5.2 / CPOB',
  pingCycle: 'Tiap 30 Detik',
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-5 shadow-sm space-y-5 border border-surface-container/60">
    <!-- Monitor Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
        </span>
        <span class="font-label-md text-label-md font-bold text-on-surface">
          Running Monitor: AKTIF
        </span>
      </div>
      <span class="font-label-sm text-label-sm px-2 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-semibold shadow-2xs">
        Sensor Terkoneksi
      </span>
    </div>

    <p class="font-body-sm text-body-sm text-on-surface-variant">
      Data IoT &amp; GPS dipancarkan setiap 30 detik secara otomatis ke Control Tower Satria Pharma &amp; AI Excursion Engine.
    </p>

    <!-- Temperature Hero Readout -->
    <div class="bg-secondary-fixed/20 p-5 rounded-xl text-center flex flex-col items-center justify-center border border-secondary/20">
      <div class="flex items-center gap-1 text-secondary font-label-sm text-label-sm font-bold uppercase tracking-wider mb-1">
        <span class="material-symbols-outlined text-[16px]">thermostat</span>
        <span>Suhu Probe Box Farmasi</span>
      </div>

      <div class="font-telemetry-value text-[48px] leading-tight font-bold text-secondary my-1 font-mono">
        {{ currentTemp.toFixed(1) }}°C
      </div>

      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm font-bold shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-secondary"></span>
        <span>Controlled Chilled (Ambang 2.0°C - 8.0°C)</span>
      </span>
    </div>

    <!-- Stability History Chart Visual (Inline SVG) -->
    <div class="space-y-2">
      <div class="flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
        <span>Tren Stabilitas (60 Menit Terakhir)</span>
        <span class="font-bold text-secondary">Stabil (+0.3°C)</span>
      </div>

      <div class="bg-surface-container-low p-3 rounded-lg border border-surface-container/40">
        <div class="h-16 w-full flex items-end">
          <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 300 80">
            <!-- Safe Range Strip (Green shade) -->
            <rect class="text-secondary/10" fill="currentColor" height="60" rx="4" width="300" x="0" y="10"></rect>
            <!-- Guideline Boundaries (Upper & Lower 2-8°C) -->
            <line class="text-secondary/30" stroke="currentColor" stroke-dasharray="3,3" stroke-width="1" x1="0" x2="300" y1="15" y2="15"></line>
            <line class="text-secondary/30" stroke="currentColor" stroke-dasharray="3,3" stroke-width="1" x1="0" x2="300" y1="65" y2="65"></line>
            <!-- Temperature Curve: 4.2 -> 4.3 -> 4.4 -> 4.5 -->
            <polyline
              class="text-secondary"
              fill="none"
              points="10,50 80,47 160,43 240,40 290,38"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="3"
            ></polyline>
            <!-- Data Points -->
            <circle class="text-secondary" cx="10" cy="50" fill="currentColor" r="4"></circle>
            <circle class="text-secondary" cx="80" cy="47" fill="currentColor" r="4"></circle>
            <circle class="text-secondary" cx="160" cy="43" fill="currentColor" r="4"></circle>
            <circle class="text-secondary" cx="240" cy="40" fill="currentColor" r="4"></circle>
            <circle class="text-secondary shadow-md" cx="290" cy="38" fill="currentColor" r="5"></circle>
          </svg>
        </div>

        <div class="flex justify-between font-label-sm text-label-sm text-on-surface-variant mt-2 pt-1 font-mono">
          <span>13:45 (4.2°C)</span>
          <span>14:05 (4.3°C)</span>
          <span>14:25 (4.4°C)</span>
          <span class="font-bold text-on-surface">14:45 (4.5°C)</span>
        </div>
      </div>
    </div>

    <!-- Hardware Telemetry Specs Grid -->
    <div class="grid grid-cols-2 gap-3 pt-1">
      <div class="bg-surface-container-low p-2.5 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">ID Probe IoT</span>
        <span class="font-label-md text-label-md font-bold text-on-surface font-mono">{{ probeId }}</span>
      </div>

      <div class="bg-surface-container-low p-2.5 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">Baterai Logger</span>
        <span class="font-label-md text-label-md font-bold text-secondary flex items-center gap-1 font-mono">
          <span class="material-symbols-outlined text-[16px]">battery_charging_full</span>
          {{ batteryPct }}%
        </span>
      </div>

      <div class="bg-surface-container-low p-2.5 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">Protokol</span>
        <span class="font-label-md text-label-md font-bold text-on-surface">{{ protocol }}</span>
      </div>

      <div class="bg-surface-container-low p-2.5 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">Siklus Ping</span>
        <span class="font-label-md text-label-md font-bold text-on-surface">{{ pingCycle }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    sensorId?: string
    currentTemp?: number
    targetTemp?: number
    batteryPct?: number
    calibrationExpiry?: string
  }>(),
  {
    sensorId: 'SNS-CO-8821',
    currentTemp: 4.5,
    targetTemp: 4.0,
    batteryPct: 94,
    calibrationExpiry: 'Aktif s/d 2027',
  }
)
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-xs border border-surface-container flex flex-col justify-between">
    <div>
      <!-- Telemetry Header -->
      <div class="flex items-center justify-between mb-space-md">
        <div class="flex items-center gap-space-xs">
          <span class="material-symbols-outlined text-primary text-[20px]">thermostat</span>
          <span class="font-headline-sm text-headline-sm text-on-surface font-extrabold tracking-tight">Sensor Telemetri IoT</span>
        </div>
        <span class="font-label-sm text-[11px] text-on-surface-variant bg-surface-container-low px-2.5 py-0.5 rounded font-mono font-bold border border-surface-container">
          ID: {{ sensorId }}
        </span>
      </div>

      <!-- Main Temperature Big Display -->
      <div class="bg-surface-container-low/70 rounded-xl p-space-md flex items-center justify-between mb-space-md border border-surface-container">
        <div class="flex flex-col">
          <span class="font-label-sm text-[11px] text-on-surface-variant uppercase font-bold tracking-wider">Suhu Saat Ini</span>
          <div class="flex items-baseline gap-1 mt-0.5">
            <span class="font-telemetry-value text-[32px] text-secondary font-extrabold tracking-tight">
              {{ currentTemp.toFixed(1) }}&deg;C
            </span>
            <span class="font-label-md text-label-md text-on-surface-variant font-medium">/ Target {{ targetTemp.toFixed(1) }}&deg;C</span>
          </div>
        </div>
        <div class="flex flex-col items-end">
          <div class="px-2.5 py-1 rounded-full bg-secondary-container/50 text-on-secondary-container font-label-sm text-[11px] font-bold flex items-center gap-1.5 shadow-2xs border border-secondary-container">
            <span class="w-2 h-2 rounded-full bg-secondary"></span>
            Aman (2.0&deg;C &ndash; 8.0&deg;C)
          </div>
          <span class="font-body-sm text-[11px] text-on-surface-variant mt-1.5 font-medium">
            Baterai Sensor: <strong class="text-on-surface font-bold">{{ batteryPct }}%</strong>
          </span>
        </div>
      </div>

      <!-- Clean Temperature Telemetry SVG Line Chart -->
      <div class="flex flex-col gap-space-xs mb-space-sm">
        <div class="flex items-center justify-between text-on-surface-variant font-label-sm text-[11px] px-1 font-semibold">
          <span>Batas Atas: 8.0&deg;C</span>
          <span class="text-secondary font-bold">Ambang CDOB Stabil</span>
        </div>

        <div class="relative w-full h-36 bg-surface-container-low/40 rounded-lg p-2 overflow-hidden border border-surface-container">
          <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 320 120">
            <!-- CDOB Upper Limit 8.0°C line (normalized ~ y=18) -->
            <line x1="0" y1="18" x2="320" y2="18" stroke="#E3BDC2" stroke-width="1" stroke-dasharray="4,4" />
            <text x="5" y="14" fill="#8F6F73" font-family="Plus Jakarta Sans" font-size="9" font-weight="600">8.0°C (Max)</text>

            <!-- CDOB Lower Limit 2.0°C line (normalized ~ y=102) -->
            <line x1="0" y1="102" x2="320" y2="102" stroke="#E3BDC2" stroke-width="1" stroke-dasharray="4,4" />
            <text x="5" y="114" fill="#8F6F73" font-family="Plus Jakarta Sans" font-size="9" font-weight="600">2.0°C (Min)</text>

            <!-- Ideal Target 4.0°C -->
            <line x1="0" y1="65" x2="320" y2="65" stroke="#CBD5E1" stroke-width="0.75" stroke-dasharray="2,2" opacity="0.6" />

            <!-- Gradient Definition -->
            <defs>
              <linearGradient id="telemetryGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#82F5C1" stop-opacity="0.4" />
                <stop offset="100%" stop-color="#82F5C1" stop-opacity="0.0" />
              </linearGradient>
            </defs>

            <!-- Filled Area Polygon -->
            <polygon points="20,62 85,60 155,58 230,56 300,56 300,102 20,102" fill="url(#telemetryGradient)" />

            <!-- Main Telemetry Path -->
            <polyline points="20,62 85,60 155,58 230,56 300,56" fill="none" stroke="#006C4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

            <!-- Data Points -->
            <circle cx="20" cy="62" r="3.5" fill="#FFFFFF" stroke="#006C4A" stroke-width="2" />
            <circle cx="85" cy="60" r="3.5" fill="#FFFFFF" stroke="#006C4A" stroke-width="2" />
            <circle cx="155" cy="58" r="3.5" fill="#FFFFFF" stroke="#006C4A" stroke-width="2" />
            <circle cx="230" cy="56" r="3.5" fill="#FFFFFF" stroke="#006C4A" stroke-width="2" />

            <!-- Current Live Point (15:00 - 4.5°C) with Ping Animation -->
            <circle cx="300" cy="56" r="5" fill="#006C4A" />
            <circle cx="300" cy="56" r="9" fill="#006C4A" opacity="0.25" class="animate-ping" />
          </svg>
        </div>

        <div class="flex justify-between items-center text-on-surface-variant font-label-sm text-[11px] px-2 mt-1">
          <span>11:00 (4.2&deg;)</span>
          <span>12:00 (4.3&deg;)</span>
          <span>13:00 (4.4&deg;)</span>
          <span>14:00 (4.5&deg;)</span>
          <span class="text-secondary font-bold">15:00 (4.5&deg;)</span>
        </div>
      </div>
    </div>

    <!-- Calibration Certificate Footer -->
    <div class="pt-space-sm mt-space-xs flex items-center justify-between text-on-surface-variant font-body-sm text-[12px] bg-surface-container-low p-2.5 rounded-lg border border-surface-container">
      <span class="flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-secondary">verified_user</span>
        <span>Sertifikasi Kalibrasi Sensor:</span>
      </span>
      <span class="font-label-sm text-[11px] text-secondary font-bold">{{ calibrationExpiry }}</span>
    </div>
  </div>
</template>

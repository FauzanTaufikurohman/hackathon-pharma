<script setup lang="ts">
// AdminTelemetryCurveCard component
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl border border-surface-container shadow-sm p-5 flex flex-col gap-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-primary text-2xl">device_thermostat</span>
        <div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">
            Telemetri Suhu Sensor IoT (Probe Sensirion STS-35)
          </h2>
          <span class="font-body-sm text-body-sm text-on-surface-variant">
            Interval Logging: 30 Detik • Sinkronisasi Cloud Telemetry Engine
          </span>
        </div>
      </div>
      <span class="font-label-md text-label-md bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full font-bold flex items-center gap-1.5 self-start sm:self-auto shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
        4.5°C Optimal (Cold-Chain Safe)
      </span>
    </div>

    <!-- Telemetry Key Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-3.5 bg-surface-container-low rounded-xl border border-surface-container">
      <div class="flex flex-col">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Min Recorded</span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-bold">3.8°C</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Max Recorded</span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-bold">5.1°C</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Ambang CPOB</span>
        <span class="font-headline-sm text-headline-sm text-secondary font-bold">2.0°C - 8.0°C</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Baterai Logger</span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-bold flex items-center gap-1">
          <span class="material-symbols-outlined text-secondary text-sm">battery_full</span> 96%
        </span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">MQTT Ping</span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-bold">30 Detik</span>
      </div>
    </div>

    <!-- Temperature Ribbon Graphic (SVG 90-Min Chart) -->
    <div class="flex flex-col gap-1.5 pt-2">
      <div class="flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant flex-wrap gap-1">
        <span class="font-semibold">Kurva Stabilitas Suhu 90 Menit Terakhir</span>
        <span class="text-secondary font-bold">Semua Titik Dalam Rentang Aman (Green Zone)</span>
      </div>

      <div class="relative w-full h-44 bg-surface rounded-xl border border-surface-container p-3 flex flex-col justify-between overflow-hidden shadow-2xs">
        <!-- Safe Zone Green Strip Background -->
        <div class="absolute inset-x-0 top-6 bottom-6 bg-secondary/5 border-y border-dashed border-secondary/30 pointer-events-none flex items-center justify-end pr-3">
          <span class="text-[10px] uppercase font-bold text-secondary tracking-widest">CPOB SAFE ZONE (2.0°C - 8.0°C)</span>
        </div>

        <!-- Y-Axis Labels -->
        <div class="absolute left-2 top-2 bottom-2 flex flex-col justify-between font-label-sm text-[10px] text-on-surface-variant select-none pointer-events-none">
          <span>9.0°C • Kritis</span>
          <span>8.0°C • Ambang Atas</span>
          <span>5.0°C • Nominal</span>
          <span>2.0°C • Ambang Bawah</span>
          <span>1.0°C • Beku</span>
        </div>

        <!-- SVG Continuous Spline Chart -->
        <svg class="w-full h-full pl-20 pr-4 overflow-visible" preserveAspectRatio="none" viewBox="0 0 500 130">
          <!-- Threshold guideline upper -->
          <line x1="0" y1="24" x2="500" y2="24" stroke="#ba1a1a" stroke-width="1" stroke-dasharray="4 4" opacity="0.4"></line>
          <!-- Threshold guideline lower -->
          <line x1="0" y1="108" x2="500" y2="108" stroke="#ba1a1a" stroke-width="1" stroke-dasharray="4 4" opacity="0.4"></line>

          <!-- Temperature area fill -->
          <defs>
            <linearGradient id="tempGradientLive" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#006c4a" stop-opacity="0.25"></stop>
              <stop offset="100%" stop-color="#006c4a" stop-opacity="0.0"></stop>
            </linearGradient>
          </defs>
          <polygon points="0,75 35,72 70,70 110,68 150,65 190,69 230,64 270,60 310,63 350,58 390,56 430,59 470,55 500,53 500,120 0,120" fill="url(#tempGradientLive)"></polygon>

          <!-- Temperature line curve -->
          <path d="M 0,75 Q 35,72 70,70 T 150,65 T 230,64 T 310,63 T 390,56 T 470,55 L 500,53" fill="none" stroke="#006c4a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>

          <!-- Live Ping Node -->
          <circle cx="500" cy="53" r="4.5" fill="#006c4a" stroke="#ffffff" stroke-width="2"></circle>
        </svg>

        <!-- X-Axis Timeline Markers -->
        <div class="flex justify-between pl-20 text-[10px] font-label-sm text-on-surface-variant pt-1 border-t border-surface-container">
          <span>13:45 WIB</span>
          <span>14:05 WIB</span>
          <span>14:25 WIB</span>
          <span>14:45 WIB</span>
          <span>15:05 WIB</span>
          <span class="font-bold text-secondary">Sekarang (15:15)</span>
        </div>
      </div>
    </div>
  </div>
</template>

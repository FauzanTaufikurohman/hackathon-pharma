<script setup lang="ts">
import type { Inspection } from '../../types/shipment'

const props = defineProps<{
  inspection?: Inspection
}>()
</script>

<template>
  <section class="bg-surface-container-lowest rounded-2xl p-6 sm:p-8 shadow-xs border border-surface-container">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface font-extrabold tracking-tight">
          Hasil Pemeriksaan Gudang
        </h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Tabel Data Uji Fisik &amp; Sensor IoT Awal ({{ inspection?.hub_name || 'Hub Transit BDG-01' }})
        </p>
      </div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px] font-bold">
          <span class="material-symbols-outlined text-[14px]">task_alt</span>
          <span>passed = {{ inspection?.passed ?? true }}</span>
        </span>
      </div>
    </div>

    <!-- 3-Column Boolean Status Badges (packaging, seal, label) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- Packaging Check -->
      <div class="p-4 rounded-xl bg-surface-container-low flex items-center justify-between border border-surface-container">
        <div>
          <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Integritas Kemasan</div>
          <div class="font-label-lg text-label-lg text-on-surface font-mono font-bold mt-0.5">packaging = {{ inspection?.packaging ?? true }}</div>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px] font-bold">
          <span class="material-symbols-outlined text-[16px]">check_circle</span>
          <span>Utuh &amp; Sesuai</span>
        </div>
      </div>

      <!-- Seal Check -->
      <div class="p-4 rounded-xl bg-surface-container-low flex items-center justify-between border border-surface-container">
        <div>
          <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Segel Keamanan RFID</div>
          <div class="font-label-lg text-label-lg text-on-surface font-mono font-bold mt-0.5">seal = {{ inspection?.seal ?? true }}</div>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px] font-bold">
          <span class="material-symbols-outlined text-[16px]">verified</span>
          <span>Segel Utuh</span>
        </div>
      </div>

      <!-- Label & Instructions Check -->
      <div class="p-4 rounded-xl bg-surface-container-low flex items-center justify-between border border-surface-container">
        <div>
          <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Label &amp; Barcode</div>
          <div class="font-label-lg text-label-lg text-on-surface font-mono font-bold mt-0.5">label = {{ inspection?.label ?? true }}</div>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px] font-bold">
          <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span>
          <span>Terbaca Jelas</span>
        </div>
      </div>
    </div>

    <!-- Temperature Gauge & Inspector Details Split Block -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
      <!-- Left: Telemetry Metric Box (5 cols) -->
      <div class="lg:col-span-5 p-5 rounded-xl bg-surface-container flex flex-col justify-between border border-surface-container-high">
        <div class="flex items-center justify-between mb-4">
          <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Suhu Pemeriksaan Awal</div>
          <span class="text-[11px] px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-mono font-semibold">
            {{ inspection?.sensor_device || 'Sensirion SHT40' }}
          </span>
        </div>
        <div class="flex items-baseline gap-3 my-2">
          <span class="font-display-lg text-[32px] text-secondary font-extrabold tracking-tight">
            {{ inspection?.temperature ? inspection.temperature.toFixed(1) : '4.2' }}&deg;C
          </span>
          <span class="font-label-md text-label-md text-secondary font-bold">
            Lolos Uji (Optimal)
          </span>
        </div>

        <!-- Boundary Visual Slider Indicator -->
        <div class="space-y-1.5 mt-2">
          <div class="flex justify-between font-label-sm text-[11px] text-outline font-semibold">
            <span>Min 2.0&deg;C</span>
            <span class="text-secondary font-bold">Target Range (2-8&deg;C)</span>
            <span>Max 8.0&deg;C</span>
          </div>
          <div class="w-full h-2.5 rounded-full bg-surface-container-highest relative overflow-hidden">
            <div class="absolute inset-y-0 left-[20%] right-[20%] bg-secondary-fixed"></div>
            <!-- Current needle indicator for 4.2C -->
            <div class="absolute top-0 bottom-0 left-[36.6%] w-2 bg-secondary rounded-full shadow-xs"></div>
          </div>
        </div>
      </div>

      <!-- Right: Inspector Notes & Signoff (7 cols) -->
      <div class="lg:col-span-7 p-5 rounded-xl bg-surface-container flex flex-col justify-between border border-surface-container-high">
        <div>
          <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider mb-2 font-bold">
            Catatan Verifikator Gudang
          </div>
          <p class="font-body-md text-body-md text-on-surface leading-relaxed">
            &ldquo;{{ inspection?.note || 'Kemasan styrofoam dan icepack optimal. Suhu sensor stabil dalam ambang CPOB tanpa fluktuasi selama 120 menit proses prakondisi pre-transit.' }}&rdquo;
          </p>
        </div>
        <div class="pt-4 mt-4 border-t border-surface-container-high/60 flex flex-wrap items-center justify-between gap-3 text-on-surface-variant font-body-sm text-body-sm">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-outline text-[18px]">badge</span>
            <span>Checker Gudang: <strong class="text-on-surface font-bold">{{ inspection?.checker_name || 'Hendra S. (ID: CHK-4409)' }}</strong></span>
          </div>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-outline text-[18px]">access_time</span>
            <span class="font-medium">{{ inspection?.inspected_at || '14 Okt 2026, 10:15 WIB' }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

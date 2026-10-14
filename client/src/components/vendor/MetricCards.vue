<script setup lang="ts">
import { currentFacility, mockOperationalMetrics } from '../../data/mockShipments'

const emit = defineEmits<{
  (e: 'openMonitor', awb: string): void
  (e: 'openReports'): void
}>()
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
    <!-- Card 1: Kargo Aktif Menuju Faskes -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-outline-variant/60 p-space-lg flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-md transition-all">
      <div class="flex items-start justify-between">
        <div class="flex flex-col">
          <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Kargo Aktif Menuju Faskes</span>
          <div class="flex items-baseline gap-2 mt-1.5">
            <span class="font-display-lg text-[32px] text-primary font-extrabold">{{ mockOperationalMetrics.active_shipments_count }} Paket</span>
            <span class="px-2 py-0.5 rounded-full bg-primary-fixed text-primary font-label-sm text-[11px] font-extrabold uppercase tracking-wider">
              Kritis
            </span>
          </div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-2xs group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-[24px]">local_shipping</span>
        </div>
      </div>

      <div class="flex flex-col gap-2 pt-2 border-t border-surface-container-high">
        <div class="flex items-center justify-between text-body-sm font-body-sm">
          <span class="text-on-surface-variant font-medium">Jadwal Tiba Target:</span>
          <span class="font-bold text-on-surface">{{ mockOperationalMetrics.next_arrival_time }}</span>
        </div>
        <div class="flex items-center justify-between pt-1">
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-tertiary-fixed text-on-tertiary-fixed font-bold uppercase tracking-wider">
            Pending Approval
          </span>
          <button
            type="button"
            @click="emit('openMonitor', 'PHA-260104')"
            class="font-label-md text-label-md text-primary font-bold flex items-center gap-1 hover:underline cursor-pointer group-hover:translate-x-0.5 transition-transform"
          >
            <span>Buka Monitor</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Card 2: Integritas Termal Cold-Chain -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-secondary-fixed-dim/80 p-space-lg flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-md transition-all">
      <div class="flex items-start justify-between">
        <div class="flex flex-col">
          <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Integritas Termal Cold-Chain</span>
          <div class="flex items-baseline gap-2 mt-1.5">
            <span class="font-display-lg text-[32px] text-secondary font-extrabold">{{ mockOperationalMetrics.average_temperature_str }}</span>
          </div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-secondary-fixed flex items-center justify-center text-secondary shadow-2xs group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-[24px]">device_thermostat</span>
        </div>
      </div>

      <div class="flex flex-col gap-2 pt-2 border-t border-surface-container-high">
        <!-- Visual Thermal Bar (2-8°C optimal range) -->
        <div class="w-full bg-surface-container rounded-full h-2 relative overflow-hidden">
          <div class="absolute left-[20%] w-[60%] h-full bg-secondary-fixed-dim rounded-full"></div>
          <div class="absolute left-[42%] w-3 h-full bg-secondary rounded-full shadow-xs"></div>
        </div>
        <div class="flex items-center justify-between pt-1">
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-secondary-fixed text-on-secondary-fixed font-bold uppercase tracking-wider">
            100% CDOB Patuh (2.0° – 8.0°C)
          </span>
          <span class="font-label-sm text-[11px] text-secondary font-bold">0 Insiden Deviasi</span>
        </div>
      </div>
    </div>

    <!-- Card 3: Dokumen BAP & Audit Terbit -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container-highest p-space-lg flex flex-col justify-between gap-4 relative overflow-hidden group hover:shadow-md transition-all">
      <div class="flex items-start justify-between">
        <div class="flex flex-col">
          <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Dokumen BAP &amp; Audit Terbit</span>
          <div class="flex items-baseline gap-2 mt-1.5">
            <span class="font-display-lg text-[32px] text-on-surface font-extrabold">{{ mockOperationalMetrics.available_reports_count }} Laporan</span>
            <span class="px-2 py-0.5 rounded-full bg-surface-variant text-on-surface font-label-sm text-[11px] font-bold uppercase tracking-wider">
              Siap Diunduh
            </span>
          </div>
        </div>
        <div class="w-11 h-11 rounded-xl bg-surface-container flex items-center justify-center text-on-surface-variant shadow-2xs group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-[24px]">assignment_turned_in</span>
        </div>
      </div>

      <div class="flex flex-col gap-2 pt-2 border-t border-surface-container-high">
        <div class="flex items-center justify-between text-body-sm font-body-sm">
          <span class="text-on-surface-variant font-medium">Apoteker PJ:</span>
          <span class="font-bold text-on-surface truncate ml-2">{{ currentFacility.pharmacist_in_charge }}</span>
        </div>
        <div class="flex items-center justify-between pt-1">
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-surface-container-high text-on-surface font-bold uppercase tracking-wider">
            CPOB Validated
          </span>
          <span class="font-label-sm text-[11px] text-outline font-medium">Digital Hash {{ mockOperationalMetrics.digital_hash }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

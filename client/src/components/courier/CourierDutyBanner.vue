<script setup lang="ts">
import type { CourierProfile } from '../../types/shipment'
import { mockCourierProfile } from '../../data/mockShipments'

interface Props {
  profile?: CourierProfile
}

const props = withDefaults(defineProps<Props>(), {
  profile: () => mockCourierProfile,
})
</script>

<template>
  <!-- Top Readiness & Duty Banner -->
  <section class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden border border-surface-container/60">
    <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-gradient-to-br from-primary-fixed/30 to-secondary-fixed/20 blur-2xl pointer-events-none"></div>

    <div class="flex flex-col gap-1.5 z-10">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-secondary-fixed/50 text-secondary font-label-sm uppercase tracking-wider font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
          Duty Active
        </span>
        <span class="font-label-sm text-outline font-medium">{{ profile.shift_name }}</span>
      </div>
      <h1 class="font-headline-lg text-on-surface font-bold">
        Selamat Bertugas, {{ profile.name }}!
      </h1>
      <p class="font-body-md text-on-surface-variant flex flex-wrap items-center gap-2">
        <span class="font-semibold text-on-surface">ID Kurir: {{ profile.courier_code }}</span>
        <span class="text-outline-variant">•</span>
        <span>{{ profile.hub_base }}</span>
        <span class="text-outline-variant">•</span>
        <span class="text-slate-500">14 Oktober 2026</span>
      </p>
    </div>

    <!-- Readiness & Telemetry Hardware Chips -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 z-10 shrink-0">
      <div class="bg-surface-container-low px-4 py-3 rounded-xl flex items-center justify-between gap-4 border border-surface-container/60 shadow-2xs">
        <div class="flex items-center gap-3">
          <div class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
          </div>
          <div class="flex flex-col">
            <span class="font-label-md text-secondary font-bold">Armada Aktif &amp; Terkalibrasi</span>
            <span class="font-body-sm text-on-surface-variant">Cooler Box {{ profile.cooler_box_code }} (Suhu: {{ profile.current_cooler_temp }}°C)</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 bg-surface-container-low p-2 rounded-xl border border-surface-container/60">
        <div class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-surface-container-lowest shadow-sm" title="Gateway BLE IoT Terhubung">
          <span class="material-symbols-outlined text-[18px] text-secondary">bluetooth_connected</span>
          <span class="font-label-sm text-on-surface font-semibold">BLE Sync</span>
        </div>
        <div class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-surface-container-lowest shadow-sm" title="Baterai Unit IoT">
          <span class="material-symbols-outlined text-[18px] text-secondary">battery_charging_90</span>
          <span class="font-label-sm text-on-surface font-semibold">{{ profile.battery_pct }}%</span>
        </div>
      </div>
    </div>
  </section>
</template>

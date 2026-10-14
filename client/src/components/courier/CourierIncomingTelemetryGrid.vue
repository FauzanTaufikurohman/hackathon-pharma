<script setup lang="ts">
import type { CourierTaskItem } from '../../types/shipment'

interface Props {
  task: CourierTaskItem
}

defineProps<Props>()
</script>

<template>
  <div class="px-6 md:px-8 py-5 bg-surface-container-lowest grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border-b border-surface-container/60">
    <!-- Card 1: Suhu Sensor IoT Hub -->
    <div class="bg-surface-container-low p-4 rounded-xl flex items-center gap-3 border border-surface-container/40 hover:border-secondary/30 transition-colors">
      <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center shrink-0 text-secondary">
        <span class="material-symbols-outlined text-[22px]">device_thermostat</span>
      </div>
      <div>
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block font-mono">
          Suhu Sensor IoT Hub
        </span>
        <span class="font-telemetry-value text-telemetry-value text-secondary font-bold font-mono">
          {{ task.temp_actual ? `${task.temp_actual.toFixed(1)}°C` : '4.2°C' }}
        </span>
      </div>
    </div>

    <!-- Card 2: Status Segel Fisik -->
    <div class="bg-surface-container-low p-4 rounded-xl flex items-center gap-3 border border-surface-container/40 hover:border-primary/30 transition-colors">
      <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center shrink-0 text-primary">
        <span class="material-symbols-outlined text-[22px]">lock</span>
      </div>
      <div>
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block font-mono">
          Status Segel Fisik
        </span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-semibold font-mono">
          {{ task.seal_str || 'SL-8891 (Terkunci)' }}
        </span>
      </div>
    </div>

    <!-- Card 3: Volume & Berat -->
    <div class="bg-surface-container-low p-4 rounded-xl flex items-center gap-3 border border-surface-container/40">
      <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center shrink-0 text-on-surface">
        <span class="material-symbols-outlined text-[22px]">package_2</span>
      </div>
      <div>
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block font-mono">
          Volume &amp; Berat
        </span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-semibold">
          {{ task.weight_str || '2 Koli • 4.8 kg' }}
        </span>
      </div>
    </div>

    <!-- Card 4: Sertifikasi CPOB -->
    <div class="bg-surface-container-low p-4 rounded-xl flex items-center gap-3 border border-surface-container/40">
      <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center shrink-0 text-primary-container">
        <span class="material-symbols-outlined text-[22px]">verified_user</span>
      </div>
      <div>
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block font-mono">
          Sertifikasi CPOB
        </span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-semibold">
          Valid &amp; Siap Jalan
        </span>
      </div>
    </div>
  </div>
</template>

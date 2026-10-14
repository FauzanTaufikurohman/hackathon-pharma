<script setup lang="ts">
import type { CourierTaskItem } from '../../types/shipment'

interface Props {
  task: CourierTaskItem
}

defineProps<Props>()
</script>

<template>
  <div class="p-6 md:p-8 bg-surface-container-low flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-surface-container">
    <!-- Left: Identity & Codes -->
    <div class="space-y-1">
      <div class="flex items-center gap-2">
        <span class="font-label-sm text-label-sm font-mono text-primary uppercase tracking-widest font-bold">
          shipments.tracking_number
        </span>
        <span class="text-xs bg-surface-container px-2 py-0.5 rounded font-mono text-on-surface-variant">
          ColdBox ID: {{ task.coldbox_id || 'CBX-882' }}
        </span>
      </div>

      <div class="flex items-baseline gap-3 flex-wrap">
        <h1 class="font-display-lg text-display-lg font-bold text-on-surface tracking-tight">
          {{ task.awb }}
        </h1>
        <span class="font-label-md text-label-md font-mono bg-surface-container text-on-surface-variant px-2.5 py-1 rounded">
          Waybill: {{ task.waybill_number || 'ANTR-ID-9920148' }}
        </span>
      </div>
    </div>

    <!-- Right: Status & Cold-chain Badge -->
    <div class="flex items-center gap-3 flex-wrap">
      <!-- Status Badge -->
      <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-secondary-fixed text-on-secondary-fixed shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-secondary"></span>
        <span class="font-label-md text-label-md font-semibold">
          Siap Diambil di Hub (approved / assigned)
        </span>
      </div>

      <!-- Cold Chain Compliance Pill -->
      <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-secondary-container text-on-secondary-container shadow-xs font-bold">
        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">ac_unit</span>
        <span class="font-label-md text-label-md">
          {{ task.temperature_profile_label || 'Controlled Cold-Chain (2.0°C – 8.0°C)' }}
        </span>
      </div>
    </div>
  </div>
</template>

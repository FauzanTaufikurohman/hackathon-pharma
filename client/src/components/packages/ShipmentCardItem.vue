<script setup lang="ts">
import type { Shipment } from '../../types/shipment'

const props = defineProps<{
  shipment: Shipment
}>()

const emit = defineEmits<{
  (e: 'action', shipment: Shipment): void
}>()
</script>

<template>
  <div
    class="bg-white rounded-xl p-space-lg border border-slate-200/90 hover:shadow-lg transition-all flex flex-col md:flex-row md:items-center justify-between gap-space-lg"
    :class="{
      'border-l-4 border-l-pink-600': shipment.id === 1 || (shipment.category === 'transit' && shipment.action_type === 'live'),
      'border-l-4 border-l-amber-500': shipment.category === 'qc',
      'border-l-4 border-l-emerald-600': shipment.action_type === 'gps' || shipment.status_label === 'Mendekati Lokasi',
      'border-l-4 border-l-blue-600': shipment.category === 'delivered' || shipment.status === 'completed'
    }"
  >
    <!-- Left Section: Status, AWB, Title & Route -->
    <div class="flex flex-col gap-2 flex-1">
      <div class="flex items-center gap-2.5 flex-wrap">
        <!-- Status Pill -->
        <!-- In Transit -->
        <span
          v-if="shipment.id === 1"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pink-100 text-pink-800 border border-pink-200 font-label-sm text-[11px] font-bold"
        >
          <span class="w-2 h-2 rounded-full bg-pink-600 animate-pulse"></span>
          <span>Dalam Perjalanan</span>
        </span>

        <!-- QC Staging -->
        <span
          v-else-if="shipment.category === 'qc'"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-200 font-label-sm text-[11px] font-bold"
        >
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
          <span>QC Staging Gudang</span>
        </span>

        <!-- Approaching Location -->
        <span
          v-else-if="shipment.id === 3"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-200 font-label-sm text-[11px] font-bold"
        >
          <span class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce"></span>
          <span>Mendekati Lokasi</span>
        </span>

        <!-- Delivered Today -->
        <span
          v-else-if="shipment.category === 'delivered' || shipment.status === 'completed'"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-900 border border-blue-200 font-label-sm text-[11px] font-bold"
        >
          <span class="material-symbols-outlined text-[16px] text-blue-700">check_circle</span>
          <span>Tiba Hari Ini (08:30 WIB)</span>
        </span>

        <!-- Fallback Default -->
        <span
          v-else
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-high text-on-surface font-label-sm text-[11px] font-bold"
        >
          <span>{{ shipment.status_label || shipment.status }}</span>
        </span>

        <!-- AWB Mono Pill -->
        <span class="font-label-sm text-[12px] text-slate-600 font-mono bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
          AWB {{ shipment.tracking_number }}
        </span>
      </div>

      <!-- Item Title -->
      <h3 class="font-headline-md text-headline-md text-slate-900 font-extrabold tracking-tight">
        {{ shipment.item_summary }}
      </h3>

      <!-- Route Info -->
      <div class="flex items-center gap-2 text-body-sm text-[13px] text-slate-600">
        <!-- Icons based on type -->
        <span v-if="shipment.category === 'qc'" class="material-symbols-outlined text-[18px] text-amber-600">warehouse</span>
        <span v-else-if="shipment.category === 'delivered'" class="material-symbols-outlined text-[18px] text-blue-600">person_check</span>
        <span v-else-if="shipment.id === 3" class="material-symbols-outlined text-[18px] text-emerald-600">near_me</span>
        <span v-else class="material-symbols-outlined text-[18px] text-pink-600">near_me</span>

        <span>{{ shipment.route_display || `${shipment.sender_name} → ${shipment.recipient_name}` }}</span>
      </div>
    </div>

    <!-- Center Section: Temperature & Estimate (Divided) -->
    <div class="flex items-center gap-space-lg sm:gap-space-xl py-2 md:py-0 border-y md:border-y-0 md:border-x border-slate-200 md:px-space-lg shrink-0">
      <!-- Temperature Column -->
      <div class="flex flex-col gap-0.5 min-w-[90px]">
        <span class="font-label-sm text-[11px] text-slate-500 font-medium">
          {{ shipment.category === 'qc' ? 'Suhu Ruang' : (shipment.category === 'delivered' ? 'Suhu Kedatangan' : 'Suhu Sensor') }}
        </span>
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
          <span class="font-headline-sm font-extrabold text-slate-900" :class="{ 'text-emerald-700': shipment.category !== 'qc' }">
            {{ shipment.current_temperature }}&deg;C
          </span>
          <span class="text-label-sm text-[10px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
            {{ shipment.temp_label || '(Aman)' }}
          </span>
        </div>
      </div>

      <!-- Estimate Column -->
      <div class="flex flex-col gap-0.5 min-w-[90px]">
        <span class="font-label-sm text-[11px] text-slate-500 font-medium">
          {{ shipment.category === 'qc' ? 'Kesiapan Dispatch' : (shipment.category === 'delivered' ? 'Status Serah Terima' : 'Estimasi Tiba') }}
        </span>
        <!-- Transit estimate -->
        <span
          v-if="shipment.id === 1"
          class="font-headline-sm font-extrabold text-pink-700 bg-pink-50 px-2.5 py-0.5 rounded-lg border border-pink-200/80 inline-block text-center"
        >
          {{ shipment.estimate_label || '~25 Menit' }}
        </span>

        <!-- QC Readiness -->
        <span
          v-else-if="shipment.category === 'qc'"
          class="font-headline-sm font-extrabold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-lg border border-amber-200/80 inline-block text-center"
        >
          {{ shipment.estimate_label || 'Siap Kirim' }}
        </span>

        <!-- Approaching estimate -->
        <span
          v-else-if="shipment.id === 3"
          class="font-headline-sm font-extrabold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200/80 inline-block text-center"
        >
          {{ shipment.estimate_label || '~8 Menit' }}
        </span>

        <!-- Delivered status -->
        <span
          v-else
          class="font-headline-sm font-extrabold text-blue-800 bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200/80 inline-block text-center"
        >
          {{ shipment.estimate_label || 'Terverifikasi' }}
        </span>
      </div>
    </div>

    <!-- Right Section: Action Button -->
    <div class="shrink-0 flex items-center">
      <!-- Live Track Button -->
      <button
        v-if="shipment.action_type === 'live' || shipment.id === 1"
        type="button"
        @click="emit('action', shipment)"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary hover:bg-[#9a003c] active:scale-[0.99] text-white py-2.5 px-space-lg rounded-xl font-label-md text-label-md font-bold transition-all shadow-md shadow-pink-200 cursor-pointer"
      >
        <span>Lacak Live</span>
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
      </button>

      <!-- Warehouse QC Button -->
      <button
        v-else-if="shipment.action_type === 'warehouse' || shipment.category === 'qc'"
        type="button"
        @click="emit('action', shipment)"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-amber-50 hover:bg-amber-100/90 text-amber-900 border border-amber-300 py-2.5 px-space-lg rounded-xl font-label-md text-label-md font-bold transition-all shadow-xs cursor-pointer"
      >
        <span>Pantau Gudang</span>
        <span class="material-symbols-outlined text-[18px]">inventory</span>
      </button>

      <!-- GPS Tracking Button -->
      <button
        v-else-if="shipment.action_type === 'gps' || shipment.id === 3"
        type="button"
        @click="emit('action', shipment)"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white py-2.5 px-space-lg rounded-xl font-label-md text-label-md font-bold transition-all shadow-md shadow-emerald-200 cursor-pointer"
      >
        <span>Pelacakan GPS</span>
        <span class="material-symbols-outlined text-[18px]">navigation</span>
      </button>

      <!-- POD Report Button -->
      <button
        v-else
        type="button"
        @click="emit('action', shipment)"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 border border-indigo-300 py-2.5 px-space-lg rounded-xl font-label-md text-label-md font-bold transition-all shadow-xs cursor-pointer"
      >
        <span>Berita Acara POD</span>
        <span class="material-symbols-outlined text-[18px]">description</span>
      </button>
    </div>
  </div>
</template>

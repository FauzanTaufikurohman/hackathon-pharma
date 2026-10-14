<script setup lang="ts">
import { mockOperationalMetrics } from '../../data/mockShipments'
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
    <!-- Metric 1: Total Kiriman -->
    <div class="bg-gradient-to-br from-pink-50/90 to-white rounded-xl p-space-md border border-pink-200/80 shadow-xs flex items-center justify-between transition-all hover:shadow-md">
      <div class="flex flex-col gap-1">
        <span class="font-label-sm text-[11px] text-pink-700 uppercase font-bold tracking-wider">
          Paket Berjalan
        </span>
        <span class="font-headline-lg text-[26px] sm:text-[28px] text-slate-900 font-extrabold tracking-tight">
          {{ mockOperationalMetrics.active_shipments_count }} Paket
        </span>
      </div>
      <div class="w-11 h-11 rounded-xl bg-primary text-white shadow-sm shadow-pink-200 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">local_shipping</span>
      </div>
    </div>

    <!-- Metric 2: Rata-rata Suhu Rantai Dingin -->
    <div class="bg-gradient-to-br from-emerald-50/90 to-white rounded-xl p-space-md border border-emerald-200/80 shadow-xs flex items-center justify-between transition-all hover:shadow-md">
      <div class="flex flex-col gap-1">
        <span class="font-label-sm text-[11px] text-emerald-800 uppercase font-bold tracking-wider">
          Rata-rata Suhu Rantai Dingin
        </span>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-headline-lg text-[26px] sm:text-[28px] text-emerald-800 font-extrabold tracking-tight">
            {{ mockOperationalMetrics.average_temperature_str }}
          </span>
          <span class="px-2.5 py-0.5 rounded-full text-label-sm text-[11px] bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">
            Aman (2&ndash;8&deg;C)
          </span>
        </div>
      </div>
      <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-200 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">thermostat</span>
      </div>
    </div>

    <!-- Metric 3: Deviasi & Insiden Suhu -->
    <div class="bg-gradient-to-br from-indigo-50/90 to-white rounded-xl p-space-md border border-indigo-200/80 shadow-xs flex items-center justify-between transition-all hover:shadow-md">
      <div class="flex flex-col gap-1">
        <span class="font-label-sm text-[11px] text-indigo-800 uppercase font-bold tracking-wider">
          Deviasi &amp; Insiden Suhu
        </span>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-headline-lg text-[26px] sm:text-[28px] text-slate-900 font-extrabold tracking-tight">
            {{ mockOperationalMetrics.incident_count }} Kasus
          </span>
          <span class="text-indigo-700 bg-indigo-100/80 px-2 py-0.5 rounded-full text-label-sm text-[11px] font-bold border border-indigo-200/60">
            100% CDOB Patuh
          </span>
        </div>
      </div>
      <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-200 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">verified</span>
      </div>
    </div>
  </div>
</template>

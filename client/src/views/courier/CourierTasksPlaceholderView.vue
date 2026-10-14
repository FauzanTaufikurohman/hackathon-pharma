<script setup lang="ts">
import { mockShipments } from '../../data/mockShipments'

const priorityShipment = mockShipments[0]!
</script>

<template>
  <div class="flex flex-col gap-6 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-primary font-label-sm font-bold uppercase">
            Modul Tugas Kurir
          </span>
          <span class="text-slate-400 text-xs">Pangkalan Hub Cawang</span>
        </div>
        <h1 class="font-headline-lg font-bold text-slate-800 mt-1">Daftar Tugas Pengantaran (Satria)</h1>
        <p class="font-body-sm text-slate-500">
          Penugasan kargo aktif &amp; rute dingin farmasi yang ditugaskan oleh Dispatcher.
        </p>
      </div>

      <router-link
        to="/courier"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-label-md font-semibold transition-colors self-start sm:self-auto cursor-pointer"
      >
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        <span>Kembali ke Beranda Kurir</span>
      </router-link>
    </div>

    <!-- Active Task Highlight Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary-container text-white flex items-center justify-center shrink-0 shadow-sm">
          <span class="material-symbols-outlined text-[24px]">local_shipping</span>
        </div>
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <span class="font-headline-sm font-bold text-slate-900">AWB: {{ priorityShipment.tracking_number }}</span>
            <span class="px-2.5 py-0.5 rounded-full bg-secondary-fixed text-secondary font-label-sm font-bold">
              Controlled Chilled 2–8°C
            </span>
          </div>
          <div class="text-slate-700 font-medium">{{ priorityShipment.item_summary }} (Batch {{ priorityShipment.batch_number }})</div>
          <div class="text-slate-500 text-sm">Tujuan: {{ priorityShipment.recipient_name }} • Deadline 15:30 WIB</div>
        </div>
      </div>

      <router-link
        :to="`/vendor/monitor/${priorityShipment.tracking_number}?screen=2`"
        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-container text-white hover:bg-primary font-label-md font-bold transition-all shadow-sm"
      >
        <span>Lihat Telemetri IoT &amp; Rute</span>
        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
      </router-link>
    </div>
  </div>
</template>

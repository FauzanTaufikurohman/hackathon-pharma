<script setup lang="ts">
import { ref } from 'vue'

const zoomLevel = ref(1)

function handleZoomIn() {
  if (zoomLevel.value < 1.4) zoomLevel.value += 0.1
}

function handleZoomOut() {
  if (zoomLevel.value > 0.8) zoomLevel.value -= 0.1
}

function handleResetLocation() {
  zoomLevel.value = 1
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl border border-surface-container shadow-sm overflow-hidden flex flex-col">
    <!-- Header -->
    <div class="p-4 md:p-5 border-b border-surface-container-low flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface-bright">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">near_me</span>
        <div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface leading-tight font-bold">
            Peta Navigasi Armada &amp; Jalur Distribusi Aktif
          </h2>
          <span class="font-body-sm text-body-sm text-on-surface-variant">
            Hub Cawang → Tol Dalam Kota → RS Medika Sehat Salemba
          </span>
        </div>
      </div>
      <span class="font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed-variant px-3 py-1 rounded-full font-bold flex items-center gap-1.5 self-start sm:self-auto">
        <span class="w-2 h-2 rounded-full bg-secondary"></span>
        2 Armada On-Route • GPS Lock 99.8%
      </span>
    </div>

    <!-- Vector Map Container -->
    <div class="relative w-full h-80 bg-surface-container-low overflow-hidden select-none">
      <!-- Stylized Map Grid & Landmass Base with dynamic scale -->
      <div
        class="absolute inset-0 w-full h-full transition-transform duration-300 origin-center"
        :style="{ transform: `scale(${zoomLevel})` }"
      >
        <svg class="absolute inset-0 w-full h-full opacity-60" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid-pattern-map" width="40" height="40" patternUnits="userSpaceOnUse">
              <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#d2d9f4" stroke-width="0.8"></path>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="#faf8ff"></rect>
          <rect width="100%" height="100%" fill="url(#grid-pattern-map)"></rect>
          <!-- Major Arterial Roads -->
          <path d="M -20,120 Q 180,90 320,160 T 640,110 T 800,220" fill="none" stroke="#dae2fd" stroke-linecap="round" stroke-width="16"></path>
          <path d="M 120,-20 Q 220,140 380,210 T 700,340" fill="none" stroke="#dae2fd" stroke-linecap="round" stroke-width="12"></path>
        </svg>

        <!-- Interactive Route Overlay: Cawang to Salemba -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none" xmlns="http://www.w3.org/2000/svg">
          <!-- Active Toll Route Path -->
          <path d="M 90,260 C 180,240 260,190 340,170 S 490,130 580,75" fill="none" stroke="#ffb2bd" stroke-linecap="round" stroke-width="6"></path>
          <path d="M 90,260 C 180,240 260,190 340,170 S 490,130 580,75" fill="none" stroke="#b10045" stroke-dasharray="6 4" stroke-linecap="round" stroke-width="3"></path>
          <!-- Secondary Route Path -->
          <path d="M 120,290 C 240,280 320,240 420,220 S 520,210 650,210" fill="none" opacity="0.7" stroke="#68dba9" stroke-dasharray="4 4" stroke-width="3"></path>
        </svg>

        <!-- Origin Landmark (Hub Cawang) -->
        <div class="absolute left-14 bottom-10 flex flex-col items-center">
          <div class="bg-surface-container-lowest text-on-surface px-2.5 py-1 rounded-md shadow-sm border border-surface-container font-label-sm text-label-sm whitespace-nowrap font-semibold">
            Hub Cawang (Origin)
          </div>
          <div class="w-3.5 h-3.5 rounded-full bg-primary border-2 border-surface-container-lowest shadow-md mt-1"></div>
        </div>

        <!-- Destination Landmark (RS Medika Sehat) -->
        <div class="absolute right-12 top-10 flex flex-col items-center">
          <div class="w-4 h-4 rounded-full bg-secondary border-2 border-surface-container-lowest shadow-md mb-1 animate-pulse"></div>
          <div class="bg-surface-container-lowest text-on-surface px-2.5 py-1 rounded-md shadow-sm border border-secondary font-label-sm text-label-sm font-semibold flex items-center gap-1">
            <span class="material-symbols-outlined text-xs text-secondary">local_hospital</span>
            RS Medika Sehat (Instalasi Farmasi)
          </div>
        </div>

        <!-- Active Courier Marker Tooltip & Position (Armada Bambang) -->
        <div class="absolute left-1/2 top-36 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer group">
          <!-- Pulsing Target Ping -->
          <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -mt-1 flex h-8 w-8 pointer-events-none">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-60"></span>
            <span class="relative inline-flex rounded-full h-8 w-8 bg-primary/20"></span>
          </span>
          <!-- Truck Icon Bubble -->
          <div class="relative z-10 w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md">
            <span class="material-symbols-outlined text-base">local_shipping</span>
          </div>
          <!-- Context Badge Card -->
          <div class="mt-2 bg-inverse-surface text-inverse-on-surface p-2.5 rounded-lg shadow-lg font-label-sm text-label-sm flex flex-col gap-0.5 border border-outline/30 whitespace-nowrap">
            <div class="flex items-center justify-between gap-3">
              <span class="font-bold text-primary-fixed">Reefer B 9482 PBF</span>
              <span class="text-secondary-fixed bg-secondary/30 px-1.5 py-0.5 rounded text-[10px] font-bold">Active IoT</span>
            </div>
            <span class="text-surface-dim font-normal">Kurir Bambang • Kecepatan: <strong>34 km/jam</strong></span>
            <div class="flex items-center gap-2 pt-0.5 text-[11px] text-surface-container">
              <span>Sisa Jarak: <strong>4.2 KM</strong></span>
              <span>•</span>
              <span>ETA: <strong>15:10 WIB</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Controls Floating Panel -->
      <div class="absolute bottom-3 right-3 flex items-center gap-1 bg-surface-container-lowest/90 backdrop-blur-sm p-1 rounded-lg border border-surface-container shadow-sm z-10">
        <button
          type="button"
          @click="handleZoomIn"
          class="w-7 h-7 flex items-center justify-center rounded hover:bg-surface-container text-on-surface text-sm font-bold cursor-pointer"
          title="Zoom In"
        >
          +
        </button>
        <button
          type="button"
          @click="handleZoomOut"
          class="w-7 h-7 flex items-center justify-center rounded hover:bg-surface-container text-on-surface text-sm font-bold cursor-pointer"
          title="Zoom Out"
        >
          −
        </button>
        <div class="h-4 w-px bg-surface-container"></div>
        <button
          type="button"
          @click="handleResetLocation"
          class="w-7 h-7 flex items-center justify-center rounded hover:bg-surface-container text-on-surface cursor-pointer"
          title="Reset Lokasi"
        >
          <span class="material-symbols-outlined text-sm">my_location</span>
        </button>
      </div>
    </div>

    <!-- Route Status Bar -->
    <div class="px-5 py-3 bg-surface-container-low flex flex-wrap items-center justify-between gap-3 text-on-surface-variant font-label-sm text-label-sm border-t border-surface-container">
      <div class="flex items-center gap-4 flex-wrap">
        <span class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
          Suhu Reefer: <strong class="text-on-surface">4.5°C (Stabil)</strong>
        </span>
        <span class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-tertiary"></span>
          Toleransi Waktu: <strong class="text-on-surface">Aman (+4 Min)</strong>
        </span>
      </div>
      <span class="font-body-sm text-body-sm text-on-surface">
        GPS Tracker ID: <strong>TEL-JKT-88219 (Quectel L80-R)</strong>
      </span>
    </div>
  </div>
</template>

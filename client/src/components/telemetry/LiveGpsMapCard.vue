<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    originName?: string
    originTime?: string
    lastGpsPoint?: string
    lastGpsTime?: string
    destinationName?: string
    destinationEta?: string
    remainingDistance?: string
    reeferPlate?: string
    averageTemp?: string
  }>(),
  {
    originName: 'Cikarang Central Hub',
    originTime: 'Pukul 11:20 WIB',
    lastGpsPoint: 'KM 02 Tol Cawang',
    lastGpsTime: 'Pukul 15:06 WIB',
    destinationName: 'Farmasi RS Medika Sehat',
    destinationEta: 'Est. 15:30 WIB',
    remainingDistance: '8.4 KM',
    reeferPlate: 'Reefer Satria B 9482 PBF',
    averageTemp: 'Stabil 4.5°C',
  }
)
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl shadow-xs border border-surface-container overflow-hidden flex flex-col relative">
    <!-- Top Header -->
    <div class="p-space-md flex items-center justify-between bg-surface-container-lowest z-10 border-b border-surface-container">
      <div class="flex items-center gap-space-sm">
        <span class="material-symbols-outlined text-primary text-[20px]">share_location</span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-extrabold tracking-tight">Rute Distribusi Aktif</span>
      </div>
      <div class="bg-secondary-container/30 text-on-secondary-container px-3 py-1 rounded-full font-label-sm text-[11px] flex items-center gap-1.5 font-bold border border-secondary-container/50">
        <span class="material-symbols-outlined text-[14px]">timer</span>
        <span>ETA: 14 Okt 2026, 15:30 WIB (~24 menit lagi)</span>
      </div>
    </div>

    <!-- Simulated Visual Clean Cold-Chain Route Map Container -->
    <div class="relative w-full h-[380px] bg-slate-50 overflow-hidden">
      <!-- Base Stylized Map Grid Overlay -->
      <svg class="absolute inset-0 w-full h-full opacity-40 pointer-events-none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="grid-pattern-telemetry" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#CBD5E1" stroke-width="0.75" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#grid-pattern-telemetry)" />

        <!-- Road Base Background Curves -->
        <path d="M-20,320 C180,310 260,260 410,210 C520,170 650,160 850,90" fill="none" stroke="#E2E8F0" stroke-width="14" stroke-linecap="round" />
        <path d="M-20,320 C180,310 260,260 410,210 C520,170 650,160 850,90" fill="none" stroke="#FFFFFF" stroke-width="8" stroke-linecap="round" />

        <!-- Traveled Active Route (Green Completed) -->
        <path d="M-20,320 C180,310 260,260 410,210" fill="none" stroke="#82F5C1" stroke-width="5" stroke-linecap="round" />
        <path d="M-20,320 C180,310 260,260 410,210" fill="none" stroke="#006C4A" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="6,4" />

        <!-- Remaining Route To Destination (Pink/Red Dashed) -->
        <path d="M410,210 C520,170 650,160 850,90" fill="none" stroke="#B10045" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="4,6" />
      </svg>

      <!-- Origin Checkpoint Marker (Cikarang Hub) -->
      <div class="absolute left-8 bottom-12 flex flex-col items-center z-10">
        <div class="px-2.5 py-1 bg-surface-container-lowest text-on-surface font-label-sm text-[11px] font-bold rounded shadow-md mb-1.5 flex items-center gap-1.5 border border-slate-200">
          <span class="w-2 h-2 rounded-full bg-secondary"></span>
          <span>Cikarang Cold Hub</span>
        </div>
        <div class="w-4 h-4 rounded-full bg-secondary flex items-center justify-center text-on-secondary shadow-sm ring-2 ring-white">
          <span class="w-1.5 h-1.5 rounded-full bg-surface-container-lowest"></span>
        </div>
      </div>

      <!-- Courier Live Moving Pulse Marker (Tol Cawang) -->
      <div class="absolute left-[52%] top-[52%] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center z-20">
        <div class="bg-primary text-on-primary font-label-sm text-[11px] font-bold px-3 py-1 rounded-full shadow-lg mb-1.5 flex items-center gap-1.5 whitespace-nowrap">
          <span class="material-symbols-outlined text-[14px]">local_shipping</span>
          <span>{{ reeferPlate }}</span>
        </div>
        <div class="relative flex items-center justify-center">
          <span class="absolute w-12 h-12 rounded-full bg-primary/25 animate-ping"></span>
          <span class="absolute w-7 h-7 rounded-full bg-primary/40"></span>
          <div class="w-6 h-6 rounded-full bg-primary flex items-center justify-center text-on-primary shadow-md ring-2 ring-white">
            <span class="material-symbols-outlined text-[14px]">navigation</span>
          </div>
        </div>
      </div>

      <!-- Destination Marker (RS Medika Sehat) -->
      <div class="absolute right-10 top-12 flex flex-col items-center z-10">
        <div class="px-3 py-1 bg-surface-container-lowest text-on-surface font-label-sm text-[11px] rounded shadow-md mb-1.5 flex items-center gap-1.5 border border-slate-200">
          <span class="material-symbols-outlined text-primary text-[15px]">local_hospital</span>
          <span class="font-bold">RS Medika Sehat (Tujuan)</span>
        </div>
        <div class="w-6 h-6 rounded-full bg-primary-container text-on-primary flex items-center justify-center shadow-lg ring-4 ring-primary-fixed/40">
          <span class="material-symbols-outlined text-[14px]">flag</span>
        </div>
      </div>

      <!-- Overlay Route Timeline Pill -->
      <div class="absolute bottom-4 left-4 right-4 bg-surface-container-lowest/95 backdrop-blur-md p-3 rounded-xl shadow-md flex items-center justify-between text-body-sm text-[12px] border border-surface-container">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
          <span class="text-on-surface font-medium">Validasi Termal Sepanjang Tol: <strong class="text-secondary font-bold">{{ averageTemp }}</strong></span>
        </div>
        <div class="text-on-surface-variant font-label-md text-label-md hidden sm:block">
          Sisa Jarak: <strong class="text-on-surface font-bold">{{ remainingDistance }}</strong>
        </div>
      </div>
    </div>

    <!-- 3 Route Steps Footprint -->
    <div class="p-space-md bg-surface-container-lowest grid grid-cols-1 sm:grid-cols-3 gap-space-sm text-left border-t border-surface-container">
      <div class="p-2.5 rounded-lg bg-surface-container-low flex flex-col border border-surface-container">
        <span class="font-label-sm text-[10px] text-on-surface-variant uppercase font-bold tracking-wider">Asal Muatan</span>
        <span class="font-label-lg text-label-lg text-on-surface font-bold mt-0.5">{{ originName }}</span>
        <span class="font-body-sm text-[12px] text-on-surface-variant">{{ originTime }}</span>
      </div>

      <div class="p-2.5 rounded-lg bg-secondary-container/20 flex flex-col border border-secondary-container/30">
        <span class="font-label-sm text-[10px] text-on-secondary-container uppercase font-bold tracking-wider">Titik Terakhir (GPS)</span>
        <span class="font-label-lg text-label-lg text-secondary font-extrabold mt-0.5">{{ lastGpsPoint }}</span>
        <span class="font-body-sm text-[12px] text-secondary font-medium">{{ lastGpsTime }}</span>
      </div>

      <div class="p-2.5 rounded-lg bg-surface-container-low flex flex-col border border-surface-container">
        <span class="font-label-sm text-[10px] text-on-surface-variant uppercase font-bold tracking-wider">Titik Penerima</span>
        <span class="font-label-lg text-label-lg text-on-surface font-bold mt-0.5">{{ destinationName }}</span>
        <span class="font-body-sm text-[12px] text-on-surface-variant">{{ destinationEta }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  speed?: string
  distanceRemaining?: string
  currentPosition?: string
  trafficStatus?: string
  destinationQuery?: string
}

withDefaults(defineProps<Props>(), {
  speed: '28.4 km/h',
  distanceRemaining: '4.2 KM',
  currentPosition: 'Tol Cawang KM 12 → Exit Kuningan/Salemba',
  trafficStatus: 'Trafik: Lancar Terkendali',
  destinationQuery: 'RS Medika Sehat Jakarta Salemba',
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm flex flex-col border border-surface-container/60">
    <!-- Header -->
    <div class="p-4 bg-surface-container-low flex items-center justify-between border-b border-surface-container/60">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[22px]">navigation</span>
        <span class="font-headline-sm text-headline-sm font-bold text-on-surface">
          Panduan Rute Satria Pharma
        </span>
      </div>

      <div class="flex items-center gap-2">
        <span class="px-2.5 py-1 rounded-full bg-surface-container-highest text-on-surface font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
          Live GPS 4G
        </span>
      </div>
    </div>

    <!-- Map View Canvas Container -->
    <div
      class="relative w-full h-80 bg-surface-container-high overflow-hidden"
      data-location="Tol Cawang Jakarta to Salemba"
    >
      <!-- Simulated High-Tech Map Background Image -->
      <div
        class="absolute inset-0 bg-cover bg-center"
        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAt4s7AT-VDyn2yizRUGATaA0KAsMP1TqsQUaMeOiyxtFVP467FsEIypFTgHWXLe_lFDzCxTPmwzildK3eBBDLBm551nU3x96JlQeTxkAfoW5DuhG4cb2ZFB-eJ0MT9zjAjczorGPwAZbUrooK_rRQJXh17rxF5yIecEN_lvAoKKI9Wz-7QMFAVsSyFHOiUg4dGG7r4-_HMDAdPxco9SJBTmkaOzdyToqNnHy0lDj706dOlqYGlsEw-uQ')"
      ></div>

      <!-- Route Overlay Graphics -->
      <div class="absolute inset-0 bg-on-background/20 backdrop-blur-[0.5px] p-4 flex flex-col justify-between pointer-events-none">
        <!-- Top Floating Telemetry Speed & Distance Pill -->
        <div class="flex items-center gap-2 self-start bg-surface-container-lowest/95 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-md text-on-surface border border-surface-container/60">
          <span class="material-symbols-outlined text-secondary text-[20px]">speed</span>
          <div>
            <span class="font-label-sm text-label-sm text-on-surface-variant block leading-none">Kecepatan Armada</span>
            <span class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ speed }}</span>
          </div>
          <div class="h-6 w-px bg-surface-container-high mx-1"></div>
          <div>
            <span class="font-label-sm text-label-sm text-on-surface-variant block leading-none">Jarak Tersisa</span>
            <span class="font-headline-sm text-headline-sm font-bold text-primary">{{ distanceRemaining }}</span>
          </div>
        </div>

        <!-- Bottom Route Location Bar -->
        <div class="bg-surface-container-lowest/95 backdrop-blur-md p-3.5 rounded-xl shadow-md flex items-center justify-between border border-surface-container/60 flex-wrap gap-2">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-primary-fixed text-primary flex items-center justify-center shrink-0 shadow-2xs">
              <span class="material-symbols-outlined text-[20px]">explore</span>
            </div>
            <div>
              <span class="font-label-sm text-label-sm uppercase font-bold text-on-surface-variant block">Posisi Saat Ini</span>
              <p class="font-label-lg text-label-lg font-bold text-on-surface leading-snug">
                {{ currentPosition }}
              </p>
            </div>
          </div>
          <span class="hidden sm:inline-flex px-3 py-1 bg-surface-container text-on-surface-variant rounded-md font-label-sm text-label-sm font-semibold">
            {{ trafficStatus }}
          </span>
        </div>
      </div>
    </div>

    <!-- Direction & Navigation Actions -->
    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-surface-container-lowest border-t border-surface-container/60">
      <a
        :href="`https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destinationQuery)}`"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center justify-center gap-2 px-4 py-3 bg-surface-container hover:bg-surface-container-high text-on-surface font-label-lg text-label-lg font-bold rounded-lg transition-colors shadow-2xs"
      >
        <span class="material-symbols-outlined text-[20px] text-primary">map</span>
        <span>Buka Google Maps</span>
      </a>

      <a
        :href="`https://waze.com/ul?q=${encodeURIComponent(destinationQuery)}`"
        target="_blank"
        rel="noopener noreferrer"
        class="flex items-center justify-center gap-2 px-4 py-3 bg-surface-container hover:bg-surface-container-high text-on-surface font-label-lg text-label-lg font-bold rounded-lg transition-colors shadow-2xs"
      >
        <span class="material-symbols-outlined text-[20px] text-secondary">alt_route</span>
        <span>Buka Navigasi Waze</span>
      </a>
    </div>
  </div>
</template>

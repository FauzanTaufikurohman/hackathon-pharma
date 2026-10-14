<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  awb: string
  completionTime?: string
  tempReading?: number
  sealCode?: string
  pharmacistName?: string
  pharmacistNipa?: string
  facilityName?: string
}

withDefaults(defineProps<Props>(), {
  completionTime: '15:18 WIB',
  tempReading: 4.4,
  sealCode: 'SL-8891',
  pharmacistName: 'Apt. Siti Rahma, S.Farm',
  pharmacistNipa: '19890412/APJ-RSMS',
  facilityName: 'RS Medika Sehat Jakarta',
})

const activeMode = ref<'normal' | 'return'>('normal')
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm space-y-6 border border-surface-container/60">
    <!-- Header & CDOB Badge -->
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">
          Kepatuhan Regulasi BPOM
        </div>
        <h2 class="font-headline-md text-headline-md text-on-surface font-bold mt-0.5">
          Form Bukti Serah Terima (e-POD) &amp; Validasi CDOB
        </h2>
      </div>

      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-semibold shadow-2xs shrink-0">
        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
        CDOB Terverifikasi
      </span>
    </div>

    <!-- Mode Toggle: Normal vs Retur -->
    <div class="grid grid-cols-2 p-1 bg-surface-container-low rounded-xl border border-surface-container/40">
      <button
        type="button"
        @click="activeMode = 'normal'"
        class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg font-label-md text-label-md transition-all cursor-pointer font-bold"
        :class="activeMode === 'normal'
          ? 'bg-surface-container-lowest text-primary shadow-xs'
          : 'text-on-surface-variant hover:text-on-surface'"
      >
        <span class="material-symbols-outlined text-base">check_circle</span>
        <span>Serah Terima Normal</span>
      </button>

      <button
        type="button"
        @click="activeMode = 'return'"
        class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg font-label-md text-label-md transition-all cursor-pointer font-bold"
        :class="activeMode === 'return'
          ? 'bg-surface-container-lowest text-primary shadow-xs'
          : 'text-on-surface-variant hover:text-on-surface'"
      >
        <span class="material-symbols-outlined text-base">assignment_return</span>
        <span>Retur Gudang</span>
      </button>
    </div>

    <!-- Form Verification Details -->
    <div class="space-y-4">
      <!-- Petugas Apoteker Card -->
      <div class="bg-surface-container-low p-4 rounded-xl flex items-start justify-between gap-4 border border-surface-container/40">
        <div class="space-y-1">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold block">
            Petugas Penerima Farmasi Resmi (APJ)
          </span>
          <div class="font-headline-sm text-headline-sm text-on-surface font-bold">
            {{ pharmacistName }}
          </div>
          <div class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-2 flex-wrap">
            <span>{{ facilityName }}</span>
            <span>•</span>
            <span class="font-mono text-xs">NIPA: {{ pharmacistNipa }}</span>
          </div>
        </div>

        <div class="w-9 h-9 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0 shadow-2xs">
          <span class="material-symbols-outlined text-xl">medical_services</span>
        </div>
      </div>

      <!-- Suhu & Segel Duo Card -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- Suhu Fisik -->
        <div class="bg-surface-container-low p-4 rounded-xl space-y-1 border border-surface-container/40">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold block">
            Suhu Fisik Serah Terima
          </span>
          <div class="flex items-baseline gap-2">
            <span class="font-telemetry-value text-telemetry-value text-secondary font-bold font-mono">
              {{ tempReading.toFixed(1) }}°C
            </span>
            <span class="font-body-sm text-body-sm text-on-surface-variant">IoT #TH-902</span>
          </div>
          <div class="inline-flex items-center gap-1 text-xs text-on-secondary-container bg-secondary-container px-2 py-0.5 rounded-full font-semibold">
            <span class="material-symbols-outlined text-sm">thermostat</span>
            <span>Lolos CPOB (2.0° - 8.0°C)</span>
          </div>
        </div>

        <!-- Kondisi Segel -->
        <div class="bg-surface-container-low p-4 rounded-xl space-y-1 border border-surface-container/40">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold block">
            Kondisi Segel Boks
          </span>
          <div class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-1.5 font-bold font-mono">
            <span class="material-symbols-outlined text-secondary text-xl">lock</span>
            <span>{{ sealCode }}</span>
          </div>
          <div class="inline-flex items-center gap-1 text-xs text-on-secondary-container bg-secondary-container px-2 py-0.5 rounded-full font-semibold">
            <span class="material-symbols-outlined text-sm">verified_user</span>
            <span>Utuh &amp; Sesuai Manifest</span>
          </div>
        </div>
      </div>

      <!-- Dokumentasi Foto & Boks -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold">
            Dokumentasi Foto Serah Terima Fisik
          </span>
          <span class="font-label-sm text-label-sm text-secondary font-bold flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">location_on</span>
            <span>Geo-Tagged Valid</span>
          </span>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <!-- Photo 1 -->
          <div class="relative group rounded-xl overflow-hidden bg-surface-container-high aspect-video shadow-xs border border-surface-container/60">
            <img
              class="w-full h-full object-cover"
              alt="Boks Vaksin #BX-01"
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuC6I3ZdAyE_fXfAVRm4MYTHaCZ1DY8w9hGvtnQClFq-HGUFxaf0TibWgutUXTVe_4CxHcimC7Lb-8W2-64xE4S5CoSUp-pwVCEgCLsTaTpP-XrcA36rtNW-hem1bMCFwj_ivmjH8ZmAcVmQp5g-WnPMhOAf3CzQ3mqm81aniQmC3kN6RHUDSDwphUsui1aCK3M0UWoJfdMzxhj5sIDA2fMzpyyYdcO1kooh4aGZVrsqVq8GwTf51DxXeg"
              loading="lazy"
            />
            <div class="absolute inset-0 bg-inverse-surface/40 flex flex-col justify-end p-2.5 text-inverse-on-surface">
              <span class="font-label-sm text-label-sm font-semibold">Boks Vaksin #BX-01</span>
              <span class="text-[10px] opacity-80 font-mono">14/10/2026 15:16:22 WIB</span>
            </div>
          </div>

          <!-- Photo 2 -->
          <div class="relative group rounded-xl overflow-hidden bg-surface-container-high aspect-video shadow-xs border border-surface-container/60">
            <img
              class="w-full h-full object-cover"
              alt="Inspeksi Segel SL-8891"
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuDTOgwSQjoJLqKgis2rLUvej38QhxlL9rG2OwNVYWs2WdcJklUugG84V879p89hCOFJX7lj5iTRzoynk0PdjLnHMr1un8Dbb6oVgcIcEGrXtqU9Si-e3bJ1yDLvgHitT2uEwn80HBITXg0rj5TZaMd7U9jaEp0y_HWZk9ig26sDCckdqC9FId-WEUGeYBceppuIhKA60Cn1roFtcSn7BaEEYmGS9Wg_pFHGwPgcmebesITLwBO8M5Kvyw"
              loading="lazy"
            />
            <div class="absolute inset-0 bg-inverse-surface/40 flex flex-col justify-end p-2.5 text-inverse-on-surface">
              <span class="font-label-sm text-label-sm font-semibold">Inspeksi Segel SL-8891</span>
              <span class="text-[10px] opacity-80 font-mono">14/10/2026 15:17:05 WIB</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tanda Tangan Digital Apoteker -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-semibold">
            Tanda Tangan Digital Apoteker (NIPA Disahkan)
          </span>
          <span class="font-mono text-xs text-on-surface-variant">SHA256: 8f4a...91b2</span>
        </div>

        <div class="relative bg-surface-container-low rounded-xl p-4 flex flex-col items-center justify-center min-h-[110px] overflow-hidden border border-surface-container/40">
          <!-- Digital Signature Vector Representation -->
          <svg class="w-48 h-16 text-on-surface opacity-80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 200 60">
            <path d="M15 45 C 30 10, 40 50, 55 25 C 65 10, 75 35, 90 20 C 105 10, 110 40, 130 15 C 145 0, 155 35, 175 25 C 185 20, 190 28, 195 22"></path>
            <path d="M40 50 L 170 48"></path>
          </svg>

          <div class="absolute bottom-2 right-3 flex items-center gap-1 text-[11px] font-semibold text-secondary bg-surface-container-lowest/90 px-2 py-0.5 rounded shadow-2xs border border-surface-container/40">
            <span class="material-symbols-outlined text-xs">verified</span>
            <span>Tervalidasi Digital Token</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Completion Button (Completed State) -->
    <button
      type="button"
      disabled
      class="w-full py-3.5 px-4 rounded-xl bg-surface-container-high text-on-surface-variant font-label-lg text-label-lg flex items-center justify-center gap-2 cursor-not-allowed border border-surface-container-highest font-bold"
    >
      <span class="material-symbols-outlined text-secondary text-xl">task_alt</span>
      <span>Tugas Pengiriman Selesai &amp; Ditutup ({{ completionTime }})</span>
    </button>
  </div>
</template>

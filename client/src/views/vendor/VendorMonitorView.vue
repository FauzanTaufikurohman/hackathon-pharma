<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Shipment } from '../../types/shipment'
import { mockShipments } from '../../data/mockShipments'

const route = useRoute()
const router = useRouter()

const awb = computed(() => (route.params.awb as string) || 'PHA-260104')
const activeScreen = ref<number>(Number(route.query.screen) || 1)

const shipment = computed<Shipment>(() => {
  return mockShipments.find(s => s.tracking_number === awb.value) || mockShipments[0]!
})

function switchScreen(screenNumber: number) {
  activeScreen.value = screenNumber
  router.replace({ query: { screen: screenNumber.toString() } })
}
</script>

<template>
  <div class="w-full flex flex-col gap-space-lg pb-space-xl">
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
      <div class="flex items-center gap-3">
        <router-link
          to="/"
          class="w-10 h-10 rounded-xl bg-surface-container hover:bg-surface-variant text-on-surface flex items-center justify-center transition-colors border border-surface-container-high"
        >
          <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </router-link>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="font-display-lg text-[24px] sm:text-[28px] font-extrabold text-on-surface">
              Monitor Kargo: {{ shipment.tracking_number }}
            </h1>
            <span class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-primary font-label-sm text-[11px] font-bold uppercase">
              Prioritas Dingin
            </span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            {{ shipment.item_summary }} &bull; {{ shipment.sender_name }}
          </p>
        </div>
      </div>

      <!-- Live Temp Pill -->
      <div class="flex items-center gap-2 px-3.5 py-2 bg-secondary-fixed text-on-secondary-fixed rounded-2xl self-start sm:self-auto font-bold text-label-md shadow-2xs">
        <span class="w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
        <span>Suhu Aktif: +{{ shipment.current_temperature }}&deg;C (Aman 2&deg;&ndash;8&deg;C)</span>
      </div>
    </div>

    <!-- 3 Screen Navigation Tabs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-surface-container-low p-1.5 rounded-2xl border border-surface-container">
      <button
        @click="switchScreen(1)"
        class="py-2.5 px-4 rounded-xl font-label-md text-label-md font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
        :class="activeScreen === 1 ? 'bg-primary text-on-primary shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'"
      >
        <span class="material-symbols-outlined text-[18px]">fact_check</span>
        <span>1. Kesiapan Faskes</span>
      </button>

      <button
        @click="switchScreen(2)"
        class="py-2.5 px-4 rounded-xl font-label-md text-label-md font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
        :class="activeScreen === 2 ? 'bg-primary text-on-primary shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'"
      >
        <span class="material-symbols-outlined text-[18px]">sensors</span>
        <span>2. Lacak Suhu &amp; GPS</span>
      </button>

      <button
        @click="switchScreen(3)"
        class="py-2.5 px-4 rounded-xl font-label-md text-label-md font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
        :class="activeScreen === 3 ? 'bg-primary text-on-primary shadow-xs' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high'"
      >
        <span class="material-symbols-outlined text-[18px]">verified</span>
        <span>3. Laporan Audit &amp; BAP</span>
      </button>
    </div>

    <!-- Screen 1: Monitor Kesiapan Faskes -->
    <div v-if="activeScreen === 1" class="bg-surface-container-lowest rounded-2xl p-space-lg border border-surface-container shadow-xs flex flex-col gap-space-md">
      <div class="flex items-center gap-2 text-primary font-bold">
        <span class="material-symbols-outlined text-[24px]">fact_check</span>
        <h2 class="text-headline-sm font-bold">Verifikasi Kesiapan Ruang Pendingin &amp; Petugas Faskes</h2>
      </div>
      <p class="font-body-md text-body-md text-on-surface-variant">
        Kurir Satria Pharma (B 9281 PFA) dijadwalkan tiba pukul <strong class="text-on-surface">15:30 WIB</strong>. Pastikan instalasi farmasi siap menerima kargo sesuai standar CDOB.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md pt-2">
        <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
          <span class="font-label-md text-label-md font-bold text-on-surface">Checklist Kesiapan:</span>
          <label class="flex items-center gap-2 text-body-sm text-on-surface cursor-pointer">
            <input type="checkbox" checked class="w-4 h-4 text-primary rounded" />
            <span>Chiller / Vaccine Storage 2&deg;C &ndash; 8&deg;C tersedia &amp; suhu stabil</span>
          </label>
          <label class="flex items-center gap-2 text-body-sm text-on-surface cursor-pointer">
            <input type="checkbox" checked class="w-4 h-4 text-primary rounded" />
            <span>Apoteker Penanggung Jawab (APJ) hadir di lokasi</span>
          </label>
          <label class="flex items-center gap-2 text-body-sm text-on-surface cursor-pointer">
            <input type="checkbox" checked class="w-4 h-4 text-primary rounded" />
            <span>Alat kalibrasi termometer sekunder siap</span>
          </label>
        </div>

        <div class="p-4 rounded-xl bg-secondary-fixed/30 border border-secondary/20 flex flex-col justify-between gap-2">
          <div>
            <span class="font-label-md text-label-md font-bold text-secondary">Status Konfirmasi Kesiapan:</span>
            <p class="text-body-sm text-on-surface-variant mt-1">
              Fasilitas Anda telah mencatatkan kesiapan penerimaan kargo vaksin.
            </p>
          </div>
          <div class="flex items-center gap-1.5 text-secondary font-bold text-label-md">
            <span class="material-symbols-outlined text-[18px]">verified</span>
            <span>Terkonfirmasi (Data siap diserahkan ke kurir)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Screen 2: Lacak Suhu & GPS Satria -->
    <div v-if="activeScreen === 2" class="bg-surface-container-lowest rounded-2xl p-space-lg border border-surface-container shadow-xs flex flex-col gap-space-md">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-secondary font-bold">
          <span class="material-symbols-outlined text-[24px]">sensors</span>
          <h2 class="text-headline-sm font-bold">Telemetri IoT Rute &amp; Suhu Kurir Satria</h2>
        </div>
        <span class="text-label-sm font-bold text-secondary bg-secondary-fixed px-3 py-1 rounded-full">
          Transmisi Tiap 10 Detik
        </span>
      </div>

      <div class="h-64 bg-surface-container-low rounded-xl flex items-center justify-center border border-surface-container relative overflow-hidden">
        <div class="text-center p-4">
          <span class="material-symbols-outlined text-[48px] text-secondary animate-bounce">near_me</span>
          <p class="font-headline-sm text-on-surface font-bold mt-2">Armada Reefer Satria: B 9281 PFA</p>
          <p class="text-body-sm text-on-surface-variant">Posisi: Tol Jakarta-Cikampek KM 42 &bull; Kecepatan: 34.5 km/jam &bull; Suhu: +4.2&deg;C</p>
        </div>
      </div>
    </div>

    <!-- Screen 3: Laporan Audit & BAP -->
    <div v-if="activeScreen === 3" class="bg-surface-container-lowest rounded-2xl p-space-lg border border-surface-container shadow-xs flex flex-col gap-space-md">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-on-surface font-bold">
          <span class="material-symbols-outlined text-[24px]">verified</span>
          <h2 class="text-headline-sm font-bold">Dokumen Berita Acara Penerimaan (BAP) &amp; Kepatuhan CPOB</h2>
        </div>
        <button
          type="button"
          class="px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-xl font-label-md font-bold flex items-center gap-1.5 shadow-xs cursor-pointer"
        >
          <span class="material-symbols-outlined text-[18px]">download</span>
          <span>Unduh PDF BAP</span>
        </button>
      </div>

      <div class="p-4 rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
        <div class="flex items-center justify-between text-body-sm">
          <span class="text-on-surface-variant">Sertifikat Validasi:</span>
          <span class="font-bold text-on-surface">CDOB-BPOM-JKT-2024.3</span>
        </div>
        <div class="flex items-center justify-between text-body-sm">
          <span class="text-on-surface-variant">Digital Signature APJ:</span>
          <span class="font-bold text-secondary">Apt. Sarah Nadia, S.Farm (Tervalidasi SHA-256)</span>
        </div>
        <div class="flex items-center justify-between text-body-sm">
          <span class="text-on-surface-variant">Integritas Suhu Rerata:</span>
          <span class="font-bold text-secondary">4.2&deg;C (100% Sesuai Spesifikasi 2&ndash;8&deg;C)</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Shipment } from '../../types/shipment'

const props = defineProps<{
  shipment: Shipment
}>()

const emit = defineEmits<{
  (e: 'openMonitor', awb: string, screenNumber?: number): void
  (e: 'confirmReady', awb: string): void
}>()
</script>

<template>
  <div class="flex flex-col gap-space-sm">
    <!-- Section Title -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-primary animate-pulse"></span>
        <h2 class="font-headline-md text-headline-md text-on-surface font-extrabold tracking-tight">
          Pengiriman Prioritas yang Memerlukan Tindakan Anda
        </h2>
      </div>
      <span class="font-label-sm text-[11px] text-primary font-bold uppercase tracking-wider bg-primary-fixed/60 px-3 py-1 rounded-full border border-primary/20">
        1 Tugas Tertunda
      </span>
    </div>

    <!-- Main Card Body -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container p-space-lg flex flex-col gap-space-lg relative overflow-hidden">
      <!-- Accent Top Gradient Strip -->
      <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-primary-container to-secondary"></div>

      <!-- Header Bar Resi -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md pb-space-md bg-surface-container-low/50 -mx-space-lg -mt-space-lg p-space-lg border-b border-surface-container">
        <div class="flex items-start sm:items-center gap-space-md flex-col sm:flex-row">
          <div class="w-12 h-12 rounded-xl bg-primary text-on-primary flex items-center justify-center shadow-xs shrink-0">
            <span class="material-symbols-outlined text-[28px]">vaccines</span>
          </div>
          <div class="flex flex-col">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-headline-md text-headline-md text-on-surface font-extrabold tracking-tight">
                AWB: {{ shipment.tracking_number }}
              </span>
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary-fixed text-primary font-label-sm text-[11px] font-bold uppercase tracking-wider border border-primary/20">
                Prioritas Vaksin Dingin
              </span>
            </div>
            <div class="flex items-center gap-2 text-body-sm font-body-sm text-on-surface-variant mt-1 flex-wrap">
              <span class="font-bold text-on-surface">Muatan: {{ shipment.item_summary }}</span>
              <span>&bull;</span>
              <span>Asal: {{ shipment.sender_name }}</span>
              <span>&bull;</span>
              <span class="text-secondary font-bold">Tujuan: {{ shipment.recipient_name }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-space-sm self-end lg:self-auto">
          <div class="text-right flex flex-col">
            <span class="font-label-sm text-[10px] text-outline uppercase tracking-wider font-bold">Suhu Real-time IoT</span>
            <span class="font-headline-sm text-headline-sm text-secondary font-extrabold flex items-center justify-end gap-1.5">
              <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
              +{{ shipment.current_temperature }}&deg;C (Optimal)
            </span>
          </div>
        </div>
      </div>

      <!-- 5-Step Milestone Stepper -->
      <div class="flex flex-col gap-2">
        <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Tahapan Rantai Pasok Klinis:</span>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
          <!-- Step 1: Completed -->
          <div class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container-low border border-surface-container">
            <div class="flex items-center gap-1.5 text-secondary font-label-sm text-label-sm font-bold">
              <span class="material-symbols-outlined text-[16px]">check_circle</span>
              <span>1. Pre-Cooling</span>
            </div>
            <span class="text-[11px] text-outline font-normal">Suhu box stabil 3.8&deg;C</span>
          </div>

          <!-- Step 2: Active (Menunggu Konfirmasi Anda) -->
          <div class="flex flex-col gap-1 p-3 rounded-xl bg-primary-fixed/40 border border-primary/30 shadow-2xs">
            <div class="flex items-center gap-1.5 text-primary font-label-sm text-label-sm font-bold">
              <span class="material-symbols-outlined text-[16px] animate-spin">sync</span>
              <span>2. Kesiapan Faskes</span>
            </div>
            <span class="text-[11px] text-on-surface font-bold">Menunggu Konfirmasi Anda</span>
          </div>

          <!-- Step 3: Upcoming -->
          <div class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container-low/70 opacity-60 border border-surface-container">
            <div class="flex items-center gap-1.5 text-on-surface-variant font-label-sm text-label-sm font-bold">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>3. Satria Pharma</span>
            </div>
            <span class="text-[11px] text-outline font-normal">Dispatched &amp; GPS</span>
          </div>

          <!-- Step 4: Upcoming -->
          <div class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container-low/70 opacity-60 border border-surface-container">
            <div class="flex items-center gap-1.5 text-on-surface-variant font-label-sm text-label-sm font-bold">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>4. Audit Termal</span>
            </div>
            <span class="text-[11px] text-outline font-normal">Inspeksi Data Logger</span>
          </div>

          <!-- Step 5: Upcoming -->
          <div class="flex flex-col gap-1 p-3 rounded-xl bg-surface-container-low/70 opacity-60 border border-surface-container">
            <div class="flex items-center gap-1.5 text-on-surface-variant font-label-sm text-label-sm font-bold">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>5. Serah Terima</span>
            </div>
            <span class="text-[11px] text-outline font-normal">Digital e-POD &amp; BAP</span>
          </div>
        </div>
      </div>

      <!-- Action Alert Kesiapan Faskes -->
      <div class="p-space-md rounded-2xl bg-surface-container-low border border-surface-container-high flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-start gap-space-sm">
          <div class="w-11 h-11 rounded-xl bg-primary text-on-primary flex items-center justify-center shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-[24px]">notification_important</span>
          </div>
          <div class="flex flex-col">
            <span class="font-headline-sm text-headline-sm text-on-surface font-extrabold">
              Konfirmasi Kesiapan Penerimaan Farmasi
            </span>
            <p class="font-body-sm text-body-sm text-on-surface-variant max-w-xl mt-0.5 leading-relaxed">
              Kurir Satria Pharma dijadwalkan tiba pada pukul <strong class="text-on-surface font-bold">15:30 WIB</strong>. Mohon validasi ketersediaan cold-room / vaccine storage 2&deg;C&ndash;8&deg;C dan petugas apoteker penerima.
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('openMonitor', shipment.tracking_number, 1)"
          class="h-12 px-6 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md font-semibold rounded-xl shadow-xs flex items-center gap-2 cursor-pointer transition-all whitespace-nowrap active:scale-[0.98] shrink-0"
        >
          <span>Buka Halaman Monitor Pengiriman</span>
          <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
      </div>

      <!-- Akses Langsung 3 Layar Monitor Resi Ini -->
      <div class="flex flex-col gap-2 pt-1">
        <span class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Akses Langsung 3 Layar Monitor Resi Ini:</span>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm">
          <!-- Shortcut 1: Monitor Kesiapan Faskes -->
          <button
            type="button"
            @click="emit('openMonitor', shipment.tracking_number, 1)"
            class="group p-4 rounded-xl bg-surface-container hover:bg-surface-variant transition-all flex items-center gap-3 text-left cursor-pointer border border-surface-container-high hover:border-primary/30"
          >
            <div class="w-10 h-10 rounded-xl bg-primary-fixed flex items-center justify-center text-primary group-hover:scale-105 transition-transform shrink-0">
              <span class="material-symbols-outlined text-[22px]">fact_check</span>
            </div>
            <div class="flex flex-col min-w-0">
              <span class="font-label-md text-label-md text-on-surface font-bold truncate">1. Monitor Kesiapan Faskes</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant truncate">Checklist cold-box &amp; penerima</span>
            </div>
            <span class="material-symbols-outlined text-outline ml-auto text-[18px] group-hover:translate-x-0.5 group-hover:text-primary transition-all">chevron_right</span>
          </button>

          <!-- Shortcut 2: Lacak Suhu & GPS Satria -->
          <button
            type="button"
            @click="emit('openMonitor', shipment.tracking_number, 2)"
            class="group p-4 rounded-xl bg-surface-container hover:bg-surface-variant transition-all flex items-center gap-3 text-left cursor-pointer border border-surface-container-high hover:border-secondary/30"
          >
            <div class="w-10 h-10 rounded-xl bg-secondary-fixed flex items-center justify-center text-secondary group-hover:scale-105 transition-transform shrink-0">
              <span class="material-symbols-outlined text-[22px]">sensors</span>
            </div>
            <div class="flex flex-col min-w-0">
              <span class="font-label-md text-label-md text-on-surface font-bold truncate">2. Lacak Suhu &amp; GPS Satria</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant truncate">IoT Data Logger + Rute Real-time</span>
            </div>
            <span class="material-symbols-outlined text-outline ml-auto text-[18px] group-hover:translate-x-0.5 group-hover:text-secondary transition-all">chevron_right</span>
          </button>

          <!-- Shortcut 3: Laporan Audit & BAP -->
          <button
            type="button"
            @click="emit('openMonitor', shipment.tracking_number, 3)"
            class="group p-4 rounded-xl bg-surface-container hover:bg-surface-variant transition-all flex items-center gap-3 text-left cursor-pointer border border-surface-container-high hover:border-primary/30"
          >
            <div class="w-10 h-10 rounded-xl bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed group-hover:scale-105 transition-transform shrink-0">
              <span class="material-symbols-outlined text-[22px]">verified</span>
            </div>
            <div class="flex flex-col min-w-0">
              <span class="font-label-md text-label-md text-on-surface font-bold truncate">3. Laporan Audit &amp; BAP</span>
              <span class="font-body-sm text-body-sm text-on-surface-variant truncate">Sertifikasi CPOB &amp; e-Sign</span>
            </div>
            <span class="material-symbols-outlined text-outline ml-auto text-[18px] group-hover:translate-x-0.5 group-hover:text-on-tertiary-fixed transition-all">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

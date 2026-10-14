<script setup lang="ts">
import type { ShipmentStatus } from '../../types/shipment'

const props = withDefaults(
  defineProps<{
    currentStatus?: ShipmentStatus
  }>(),
  {
    currentStatus: 'pending_approval',
  }
)
</script>

<template>
  <section class="bg-surface-container-lowest rounded-2xl p-6 sm:p-8 shadow-xs border border-surface-container">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-8">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface font-extrabold tracking-tight">
          Alur Siklus Distribusi
        </h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Sesuai SOP Standar CPOB &amp; GDP Kementerian Kesehatan
        </p>
      </div>
      <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">
        Fase 2 dari 5 Selesai
      </div>
    </div>

    <!-- 5-Step Horizontal Progress Tracker -->
    <div class="relative w-full">
      <!-- Connecting Track Background -->
      <div class="absolute top-5 left-6 right-6 h-1 bg-surface-container-high -translate-y-1/2 z-0 hidden sm:block"></div>
      <!-- Progress Bar Active Segment (25%) -->
      <div class="absolute top-5 left-6 w-[25%] h-1 bg-primary -translate-y-1/2 z-0 hidden sm:block transition-all duration-500"></div>

      <div class="grid grid-cols-1 sm:grid-cols-5 gap-6 sm:gap-2 relative z-10">
        <!-- Step 1: Completed -->
        <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 group">
          <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-on-secondary shadow-xs ring-4 ring-surface-container-lowest shrink-0">
            <span class="material-symbols-outlined text-[20px]">check</span>
          </div>
          <div>
            <div class="font-label-sm text-[11px] text-secondary uppercase tracking-wider font-bold">Selesai</div>
            <div class="font-label-md text-label-md text-on-surface font-bold">Menunggu Pemeriksaan</div>
            <div class="font-body-sm text-body-sm text-outline font-mono text-[12px]">inspection</div>
          </div>
        </div>

        <!-- Step 2: Active / Current -->
        <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 group">
          <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary shadow-xs ring-4 ring-primary-fixed shrink-0">
            <span class="w-3 h-3 rounded-full bg-on-primary animate-ping"></span>
          </div>
          <div>
            <div class="font-label-sm text-[11px] text-primary uppercase tracking-wider font-bold">Tahap Aktif</div>
            <div class="font-label-md text-label-md text-primary font-bold">Menunggu Persetujuan</div>
            <div class="font-body-sm text-body-sm text-outline font-mono text-[12px]">pending_approval</div>
          </div>
        </div>

        <!-- Step 3: Upcoming -->
        <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 group opacity-60">
          <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-outline ring-4 ring-surface-container-lowest shrink-0">
            <span class="material-symbols-outlined text-[18px]">verified_user</span>
          </div>
          <div>
            <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Tahap 3</div>
            <div class="font-label-md text-label-md text-on-surface font-bold">Siap Ditugaskan</div>
            <div class="font-body-sm text-body-sm text-outline font-mono text-[12px]">approved</div>
          </div>
        </div>

        <!-- Step 4: Upcoming -->
        <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 group opacity-40">
          <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-outline ring-4 ring-surface-container-lowest shrink-0">
            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
          </div>
          <div>
            <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Tahap 4</div>
            <div class="font-label-md text-label-md text-on-surface font-bold">Dalam Perjalanan</div>
            <div class="font-body-sm text-body-sm text-outline font-mono text-[12px]">in_transit</div>
          </div>
        </div>

        <!-- Step 5: Upcoming -->
        <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 group opacity-40">
          <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-outline ring-4 ring-surface-container-lowest shrink-0">
            <span class="material-symbols-outlined text-[18px]">done_all</span>
          </div>
          <div>
            <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">Tahap 5</div>
            <div class="font-label-md text-label-md text-on-surface font-bold">Sudah Diterima</div>
            <div class="font-body-sm text-body-sm text-outline font-mono text-[12px]">delivered</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stepper Footer Note -->
    <div class="mt-8 pt-4 border-t-0 bg-surface-container-low p-4 rounded-xl flex items-center gap-3 text-on-surface-variant font-body-sm text-body-sm border border-surface-container">
      <span class="material-symbols-outlined text-secondary shrink-0">info</span>
      <span class="leading-relaxed">
        Kargo telah melewati uji fisik gudang dan sedang menunggu otorisasi dispatch resmi dari Supervisor QA Cold-Chain.
      </span>
    </div>
  </section>
</template>

<script setup lang="ts">
import { currentFacility } from '../../data/mockShipments'
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md pt-space-xs">
    <div class="flex flex-col gap-2">
      <!-- Breadcrumb & Status IoT Pill -->
      <div class="flex items-center gap-2.5 flex-wrap">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed text-label-sm font-label-sm shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
          <span>Sistem IoT Aktif &bull; Transmisi 30s</span>
        </div>
        <span class="text-body-sm text-outline hidden sm:inline">&bull;</span>
        <span class="text-body-sm font-semibold text-on-surface-variant flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px] text-primary">local_pharmacy</span>
          {{ currentFacility.name }} ({{ currentFacility.department }})
        </span>
      </div>

      <!-- Title & Description -->
      <h1 class="font-display-lg text-[28px] sm:text-[34px] leading-tight text-on-surface font-extrabold tracking-tight">
        Pusat Pantau Kargo Rantai Dingin &amp; Vaksin
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
        Selamat datang di portal penerimaan logistik dingin RS Medika Sehat. Verifikasi status kesiapan, pantau telemetri suhu real-time, dan audit sertifikasi CPOB/GDP sebelum penerimaan fisik kargo farmasi.
      </p>
    </div>

    <!-- Facility ID Badge Card -->
    <div class="flex items-center gap-3 px-4 py-3 bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container self-start md:self-auto shrink-0">
      <div class="w-10 h-10 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-[22px]">domain_verification</span>
      </div>
      <div class="flex flex-col">
        <span class="text-[10px] font-bold text-outline uppercase tracking-wider">Instalasi Farmasi</span>
        <span class="font-label-md text-label-md text-on-surface font-bold">ID: {{ currentFacility.facility_id }}</span>
      </div>
    </div>
  </div>
</template>

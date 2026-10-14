<script setup lang="ts">
import type { AuditEvent } from '../../types/shipment'
import { mockAuditEvents } from '../../data/mockShipments'

interface Props {
  events?: AuditEvent[]
}

const props = withDefaults(defineProps<Props>(), {
  events: () => mockAuditEvents,
})
</script>

<template>
  <section class="flex flex-col rounded-xl bg-surface-container-lowest p-6 shadow-sm border border-surface-container">
    <!-- Header: Title & SHA-256 Ver. -->
    <div class="flex items-start justify-between gap-4 pb-5 border-b border-surface-container-low mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">enhanced_encryption</span>
          <h2 class="font-headline-md text-headline-md font-bold text-on-surface">
            Kronologi &amp; Audit Trail Lengkap
          </h2>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
          Catatan transaksi logistik terlindungi dan tidak dapat diubah (Tabel <code class="font-mono bg-surface-container-low px-1.5 py-0.5 rounded text-primary">events</code> ledger).
        </p>
      </div>
      <span class="inline-flex items-center px-2.5 py-1 rounded bg-surface-container text-on-surface font-label-sm text-label-sm font-semibold shrink-0">
        SHA-256 Ver.
      </span>
    </div>

    <!-- Vertical Timeline -->
    <div class="relative pl-6 space-y-8 before:absolute before:left-2 before:top-3 before:bottom-3 before:w-0.5 before:bg-surface-container-high">
      <!-- Milestone 1: Deliver -->
      <div class="relative group">
        <div class="absolute -left-6 top-0 w-4 h-4 rounded-full bg-secondary flex items-center justify-center ring-4 ring-secondary-container">
          <div class="w-1.5 h-1.5 rounded-full bg-on-secondary"></div>
        </div>
        <div class="bg-surface-container-low/60 rounded-xl p-4 transition-colors group-hover:bg-surface-container-low border border-transparent group-hover:border-surface-container">
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="inline-flex items-center gap-1 font-label-md text-label-md uppercase font-bold text-secondary">
              <span class="material-symbols-outlined text-[16px]">done_all</span>
              Event: deliver (Sudah Diterima)
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant">14 Okt 2026, 15:18 WIB</span>
          </div>
          <div class="text-body-md text-on-surface font-medium">
            Kurir Bambang Suryono
          </div>
          <div class="text-body-sm text-on-surface-variant mb-2">
            Penerima: <span class="font-semibold text-on-surface">Apt. Sarah Nadia, S.Farm</span> (Instalasi Farmasi)
          </div>
          <div class="inline-flex items-center gap-3 px-3 py-1.5 rounded-lg bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-2xs">
            <span class="flex items-center gap-1 text-secondary font-bold">
              <span class="material-symbols-outlined text-[14px]">device_thermostat</span> 4.4°C
            </span>
            <span class="text-outline">/</span>
            <span class="flex items-center gap-1 text-on-surface-variant">
              <span class="material-symbols-outlined text-[14px]">lock</span> Segel: Utuh
            </span>
          </div>
        </div>
      </div>

      <!-- Milestone 2: Depart -->
      <div class="relative group">
        <div class="absolute -left-6 top-0 w-4 h-4 rounded-full bg-primary flex items-center justify-center ring-4 ring-primary-fixed">
          <div class="w-1.5 h-1.5 rounded-full bg-on-primary"></div>
        </div>
        <div class="bg-surface-container-low/60 rounded-xl p-4 transition-colors group-hover:bg-surface-container-low border border-transparent group-hover:border-surface-container">
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="inline-flex items-center gap-1 font-label-md text-label-md uppercase font-bold text-primary">
              <span class="material-symbols-outlined text-[16px]">local_shipping</span>
              Event: depart (Dalam Perjalanan)
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant">14 Okt 2026, 13:45 WIB</span>
          </div>
          <div class="text-body-md text-on-surface font-medium">
            Kurir Bambang Suryono
          </div>
          <div class="text-body-sm text-on-surface-variant mb-2">
            Armada Reefer Berinsulasi <span class="font-semibold text-on-surface">B 9482 PBF</span> (IoT Active)
          </div>
          <div class="inline-flex items-center gap-3 px-3 py-1.5 rounded-lg bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-2xs">
            <span class="flex items-center gap-1 text-secondary font-bold">
              <span class="material-symbols-outlined text-[14px]">device_thermostat</span> 4.3°C
            </span>
            <span class="text-outline">/</span>
            <span class="flex items-center gap-1 text-on-surface-variant">
              <span class="material-symbols-outlined text-[14px]">route</span> Tol Cikampek Menuju Jakarta
            </span>
          </div>
        </div>
      </div>

      <!-- Milestone 3: Approve -->
      <div class="relative group">
        <div class="absolute -left-6 top-0 w-4 h-4 rounded-full bg-on-surface-variant flex items-center justify-center ring-4 ring-surface-variant">
          <div class="w-1.5 h-1.5 rounded-full bg-surface-container-lowest"></div>
        </div>
        <div class="bg-surface-container-low/60 rounded-xl p-4 transition-colors group-hover:bg-surface-container-low border border-transparent group-hover:border-surface-container">
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="inline-flex items-center gap-1 font-label-md text-label-md uppercase font-bold text-on-surface">
              <span class="material-symbols-outlined text-[16px]">verified_user</span>
              Event: approve (Siap Ditugaskan)
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant">14 Okt 2026, 11:30 WIB</span>
          </div>
          <div class="text-body-md text-on-surface font-medium">
            Admin Operasional Farhan
          </div>
          <div class="text-body-sm text-on-surface-variant mb-2">
            Keputusan: <span class="inline-flex items-center px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed font-bold">approved</span>
          </div>
          <div class="p-2.5 rounded-lg bg-surface-container-lowest text-on-surface font-body-sm text-body-sm shadow-2xs">
            <span class="text-on-surface-variant">Justifikasi:</span> Checklist fisik &amp; kalibrasi suhu 4.2°C lolos verifikasi standar CPOB.
          </div>
        </div>
      </div>

      <!-- Milestone 4: Inspect -->
      <div class="relative group">
        <div class="absolute -left-6 top-0 w-4 h-4 rounded-full bg-outline flex items-center justify-center ring-4 ring-surface-container">
          <div class="w-1.5 h-1.5 rounded-full bg-surface-container-lowest"></div>
        </div>
        <div class="bg-surface-container-low/60 rounded-xl p-4 transition-colors group-hover:bg-surface-container-low border border-transparent group-hover:border-surface-container">
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="inline-flex items-center gap-1 font-label-md text-label-md uppercase font-bold text-on-surface-variant">
              <span class="material-symbols-outlined text-[16px]">policy</span>
              Event: inspect (Menunggu Persetujuan)
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant">14 Okt 2026, 10:15 WIB</span>
          </div>
          <div class="text-body-md text-on-surface font-medium">
            Checker Gudang Hendra
          </div>
          <div class="text-body-sm text-on-surface-variant mb-2">
            Validasi awal cold-room hub Cikarang
          </div>
          <div class="font-mono text-body-sm px-3 py-1.5 rounded-lg bg-surface-container-lowest text-on-surface shadow-2xs">
            Suhu: <span class="font-bold text-secondary">4.2°C</span> • { passed: true, packaging: true, seal: true }
          </div>
        </div>
      </div>
    </div>

    <!-- Chain Integrity Notice -->
    <div class="mt-8 pt-4 flex items-center gap-3 text-on-surface-variant font-body-sm text-body-sm border-t border-surface-container">
      <span class="material-symbols-outlined text-secondary text-[20px]">lock_reset</span>
      <span>Semua data dienkripsi asimetris dan disinkronkan ke sistem pelaporan Kementerian Kesehatan RI.</span>
    </div>
  </section>
</template>

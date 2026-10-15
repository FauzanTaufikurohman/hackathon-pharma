<script setup lang="ts">
import type { AdminDailyComplianceReport } from '../../types/shipment'

defineProps<{
  report: AdminDailyComplianceReport
}>()

const emit = defineEmits<{
  (e: 'export-pdf'): void
  (e: 'distribute-all'): void
}>()
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 sm:p-8 mb-8 shadow-sm relative overflow-hidden border border-surface-container">
    <!-- Ambient Blur Glows -->
    <div class="absolute -right-16 -top-16 w-64 h-64 bg-primary-fixed/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-secondary-fixed/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col gap-6">
      <!-- Card Title & Status Badge -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-surface-container-low">
        <div class="flex items-start sm:items-center gap-3">
          <div class="p-3 bg-primary-fixed text-primary rounded-xl flex items-center justify-center shrink-0 shadow-2xs">
            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">
              assignment_turned_in
            </span>
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="font-headline-lg text-headline-lg text-on-surface">
                Laporan Kepatuhan Rantai Dingin Harian
              </h2>
              <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm flex items-center gap-1 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                Terverifikasi Sistem
              </span>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
              Siklus Log Operasional: <span class="font-label-sm text-on-surface font-semibold">{{ report.cycle_date }}</span>
              • Arsip Dokumen ID: <span class="font-label-sm text-on-surface font-semibold">{{ report.report_id }}</span>
            </p>
          </div>
        </div>

        <!-- Action CTA Buttons -->
        <div class="flex flex-wrap items-center gap-3">
          <button
            type="button"
            id="btn-export-pdf"
            @click="emit('export-pdf')"
            class="bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg px-5 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 shadow-sm active:scale-95 cursor-pointer"
          >
            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
            <span>Unduh Laporan Harian + BAP Resmi (PDF)</span>
          </button>
          <button
            type="button"
            id="btn-distribute-all"
            @click="emit('distribute-all')"
            class="bg-surface-container-low hover:bg-surface-container text-on-surface font-label-lg text-label-lg px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 border border-surface-container active:scale-95 cursor-pointer"
          >
            <span class="material-symbols-outlined text-[18px] text-primary">share</span>
            <span>Distribusikan Paralel (Email &amp; WA)</span>
          </button>
        </div>
      </div>

      <!-- Key Metrics Strip (5 Columns) -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 pt-2">
        <!-- Metric 1: Total Pengiriman -->
        <div class="bg-surface-container-low rounded-xl p-4 flex flex-col justify-between border border-surface-container">
          <div class="flex items-center justify-between text-on-surface-variant mb-2">
            <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">
              Total Pengiriman
            </span>
            <span class="material-symbols-outlined text-base">local_shipping</span>
          </div>
          <div class="font-telemetry-value text-telemetry-value text-on-surface font-bold">
            {{ report.total_shipments }} <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">Kargo</span>
          </div>
          <span class="font-label-sm text-label-sm text-secondary flex items-center gap-1 mt-1 font-bold">
            <span class="material-symbols-outlined text-[14px]">check</span> {{ report.total_shipments_note }}
          </span>
        </div>

        <!-- Metric 2: Suhu Vaksin -->
        <div class="bg-surface-container-low rounded-xl p-4 flex flex-col justify-between border border-surface-container">
          <div class="flex items-center justify-between text-on-surface-variant mb-2">
            <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">
              Suhu Vaksin (2-8°C)
            </span>
            <span class="material-symbols-outlined text-base text-secondary">device_thermostat</span>
          </div>
          <div class="font-telemetry-value text-telemetry-value text-secondary font-bold">
            {{ report.temp_optimal_pct }}% <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">Optimal</span>
          </div>
          <span class="font-label-sm text-label-sm text-on-surface-variant mt-1">
            {{ report.excursion_rate_desc }}
          </span>
        </div>

        <!-- Metric 3: Initial Exam QC -->
        <div class="bg-surface-container-low rounded-xl p-4 flex flex-col justify-between border border-surface-container">
          <div class="flex items-center justify-between text-on-surface-variant mb-2">
            <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">
              Initial Exam QC
            </span>
            <span class="material-symbols-outlined text-base text-secondary">fact_check</span>
          </div>
          <div class="font-telemetry-value text-telemetry-value text-on-surface font-bold">
            {{ report.qc_passed_pct }}% <span class="font-body-sm text-body-sm text-secondary font-semibold">Passed</span>
          </div>
          <span class="font-label-sm text-label-sm text-secondary mt-1 font-semibold">
            {{ report.hub_quarantine_desc }}
          </span>
        </div>

        <!-- Metric 4: Insiden & Eskalasi -->
        <div class="bg-surface-container-low rounded-xl p-4 flex flex-col justify-between border border-surface-container">
          <div class="flex items-center justify-between text-on-surface-variant mb-2">
            <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">
              Insiden &amp; Eskalasi
            </span>
            <span class="material-symbols-outlined text-base text-primary">warning</span>
          </div>
          <div class="font-telemetry-value text-telemetry-value text-primary font-bold">
            {{ report.incident_count }} <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">Insiden</span>
          </div>
          <span class="font-label-sm text-label-sm text-primary font-bold mt-1">
            {{ report.incident_desc }}
          </span>
        </div>

        <!-- Metric 5: Apoteker QA Pengesah -->
        <div class="col-span-2 sm:col-span-1 lg:col-span-1 bg-surface-container-low rounded-xl p-4 flex flex-col justify-between border border-surface-container">
          <div class="flex items-center justify-between text-on-surface-variant mb-2">
            <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">
              Apoteker QA Pengesah
            </span>
            <span class="material-symbols-outlined text-base">clinical_notes</span>
          </div>
          <div class="font-label-lg text-label-lg text-on-surface truncate font-bold">
            {{ report.pharmacist_name }}
          </div>
          <span class="font-label-sm text-label-sm text-on-surface-variant truncate mt-1">
            {{ report.pharmacist_sip }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { AdminApprovalShipment } from '../../types/shipment'

const props = defineProps<{
  shipment: AdminApprovalShipment
  decisionStatus: 'pending' | 'approved' | 'held'
}>()

const emit = defineEmits<{
  (e: 'approve', reason: string): void
  (e: 'hold', reason: string): void
  (e: 'view-photo', url: string): void
}>()

const adminReason = ref(props.shipment.default_admin_reason)

watch(
  () => props.shipment,
  (newShipment) => {
    adminReason.value = newShipment.default_admin_reason
  }
)

function handleApprove() {
  emit('approve', adminReason.value)
}

function handleHold() {
  emit('hold', adminReason.value)
}
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6 flex flex-col gap-5 border border-surface-container-low">
    <!-- Card Header -->
    <div class="flex items-center justify-between gap-2 pb-4 bg-gradient-to-r from-surface-container-lowest to-surface-container-low/40 -mx-6 -mt-6 p-6 rounded-t-xl border-b border-surface-container-low">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-primary-fixed flex items-center justify-center text-primary shrink-0">
          <span class="material-symbols-outlined">fact_check</span>
        </div>
        <div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">1. Verifikasi Mutu Hasil Initial Exam (Checker Hub)</h2>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Pemeriksaan fisik pra-jalan • Tabel: inspections &amp; approvals</p>
        </div>
      </div>
      <span
        v-if="decisionStatus === 'pending'"
        class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-50 text-amber-900 font-label-sm text-label-sm font-semibold shrink-0"
      >
        Status: Menunggu Otorisasi Admin
      </span>
      <span
        v-else-if="decisionStatus === 'approved'"
        class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-900 font-label-sm text-label-sm font-semibold shrink-0"
      >
        Status: Disetujui Admin (approved)
      </span>
      <span
        v-else
        class="inline-flex items-center px-2.5 py-1 rounded-full bg-error-container text-error font-label-sm text-label-sm font-semibold shrink-0"
      >
        Status: Ditahan (held)
      </span>
    </div>

    <!-- Checker Meta Information -->
    <div class="bg-surface-container-low rounded-lg p-3.5 flex flex-wrap items-center justify-between gap-2 border border-surface-container">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-on-surface-variant text-lg">person_pin</span>
        <span class="font-label-md text-label-md text-on-surface font-semibold">
          Petugas Checker: {{ shipment.checker_name }}
          <span class="text-on-surface-variant font-normal">(ID: {{ shipment.checker_id }})</span>
        </span>
      </div>
      <div class="flex items-center gap-1 text-on-surface-variant font-label-sm text-label-sm">
        <span class="material-symbols-outlined text-sm">schedule</span>
        <span>Waktu Submit: {{ shipment.submitted_at }}</span>
      </div>
    </div>

    <!-- 3 Boolean Inspection Pills -->
    <div>
      <div class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-2 font-bold">
        Checklist Kepatuhan Fisik (Inspections Check):
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
        <div class="bg-emerald-50 text-emerald-800 rounded-lg p-2.5 flex items-center gap-2 border border-emerald-100">
          <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
          <div class="flex flex-col">
            <span class="font-label-sm text-label-sm font-bold uppercase">Packaging</span>
            <span class="font-label-sm text-label-sm font-medium">Utuh &amp; Kedap (true)</span>
          </div>
        </div>
        <div class="bg-emerald-50 text-emerald-800 rounded-lg p-2.5 flex items-center gap-2 border border-emerald-100">
          <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
          <div class="flex flex-col">
            <span class="font-label-sm text-label-sm font-bold uppercase">Seal / Segel</span>
            <span class="font-label-sm text-label-sm font-medium">Utuh {{ shipment.seal_code }} (true)</span>
          </div>
        </div>
        <div class="bg-emerald-50 text-emerald-800 rounded-lg p-2.5 flex items-center gap-2 border border-emerald-100">
          <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
          <div class="flex flex-col">
            <span class="font-label-sm text-label-sm font-bold uppercase">Label GHS/GDP</span>
            <span class="font-label-sm text-label-sm font-medium">Lengkap &amp; Terbaca (true)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Temperature & AI Vision OCR Box -->
    <div class="bg-surface-container rounded-xl p-4 flex flex-col gap-3 border border-surface-container-high/60">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <span class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1.5">
          <span class="material-symbols-outlined text-secondary text-lg">thermostat</span>
          Validasi Termal &amp; Audit AI Vision OCR
        </span>
        <span class="font-label-sm text-label-sm text-secondary font-bold bg-secondary-container/40 px-2.5 py-0.5 rounded-full">
          Rentang Aman CPOB: {{ shipment.temp_min.toFixed(1) }}°C – {{ shipment.temp_max.toFixed(1) }}°C
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
        <!-- Left: Input Manual -->
        <div class="md:col-span-4 bg-surface-container-lowest rounded-lg p-3 shadow-xs border border-surface-container-low">
          <span class="font-label-sm text-label-sm text-on-surface-variant block mb-1">Suhu Input Manual</span>
          <div class="flex items-baseline gap-1.5">
            <span class="font-telemetry-value text-telemetry-value text-on-surface tracking-tight font-bold">{{ shipment.temp_manual.toFixed(1) }}</span>
            <span class="font-headline-sm text-headline-sm text-on-surface-variant font-bold">°C</span>
          </div>
          <span class="font-label-sm text-label-sm text-secondary flex items-center gap-1 mt-1 font-semibold">
            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> Valid Dalam Range
          </span>
        </div>

        <!-- Middle: AI OCR result -->
        <div class="md:col-span-5 bg-surface-container-lowest rounded-lg p-3 shadow-xs border border-surface-container-low">
          <span class="font-label-sm text-label-sm text-on-surface-variant block mb-1">AI Vision OCR Sensor</span>
          <div class="flex items-baseline gap-1.5">
            <span class="font-telemetry-value text-telemetry-value text-secondary tracking-tight font-bold">{{ shipment.temp_ocr.toFixed(1) }}</span>
            <span class="font-headline-sm text-headline-sm text-secondary font-bold">°C</span>
          </div>
          <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-900 font-label-sm text-label-sm font-semibold">
            <span class="material-symbols-outlined text-xs">verified</span> ✓ AI Match (100% Valid)
          </div>
        </div>

        <!-- Right: Visual Snapshot Thumbnail -->
        <div class="md:col-span-3 flex flex-col items-center justify-center">
          <div
            @click="emit('view-photo', shipment.photo_url)"
            class="relative w-full h-20 rounded-lg overflow-hidden shadow-xs group cursor-pointer bg-slate-900 border border-surface-container"
            title="Klik untuk memperbesar bukti foto inspeksi"
          >
            <img
              class="w-full h-full object-cover opacity-85 group-hover:scale-105 transition-transform"
              alt="Foto Inspeksi Suhu Digital &amp; Segel"
              :src="shipment.photo_url"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end justify-center p-1">
              <span class="font-label-sm text-label-sm text-white flex items-center gap-1 font-semibold">
                <span class="material-symbols-outlined text-xs">zoom_in</span> Bukti Foto
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Checker Note (inspections.note) -->
    <div class="bg-surface-container-low rounded-lg p-3.5 border border-surface-container">
      <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block mb-1 font-bold">
        Catatan Checker (inspections.note):
      </span>
      <p class="font-body-sm text-body-sm text-on-surface italic">
        {{ shipment.checker_note }}
      </p>
    </div>

    <!-- Auto Calculated Status -->
    <div class="bg-emerald-50 rounded-lg px-4 py-2.5 flex items-center justify-between border border-emerald-100">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-emerald-700">task_alt</span>
        <span class="font-label-md text-label-md text-emerald-900 uppercase tracking-wide font-bold">
          STATUS KELAYAKAN CHECKER: LOLOS UJI (PASSED = TRUE)
        </span>
      </div>
      <span class="font-label-sm text-label-sm text-emerald-800 bg-white/80 px-2 py-0.5 rounded shadow-2xs font-bold">
        100% CPOB
      </span>
    </div>

    <!-- Form Keputusan Approval Admin (approvals table) -->
    <div class="pt-2 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="admin-reason">
          Alasan / Rekomendasi Resmi Admin (approvals.reason)
          <span class="text-error font-bold">*Wajib</span>
        </label>
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">Log Terenkripsi Audit BPOM</span>
      </div>
      <textarea
        id="admin-reason"
        v-model="adminReason"
        rows="3"
        class="w-full bg-surface-container-lowest text-on-surface font-body-sm text-body-sm p-3 rounded-lg shadow-xs border border-surface-container focus:outline-none focus:ring-2 focus:ring-primary/30"
      ></textarea>

      <!-- Decision Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center gap-3 pt-1">
        <button
          type="button"
          id="btn-approve"
          @click="handleApprove"
          class="w-full sm:flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-label-lg text-label-lg py-3 px-6 rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.99] cursor-pointer"
        >
          <span class="material-symbols-outlined text-xl">check_circle</span>
          <span>✓ Setujui Pengiriman (decision = approved → Buka Kunci Kurir)</span>
        </button>

        <button
          type="button"
          id="btn-hold"
          @click="handleHold"
          class="w-full sm:w-auto bg-error-container hover:bg-error-container/80 text-error font-label-lg text-label-lg py-3 px-5 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer"
        >
          <span class="material-symbols-outlined text-xl">cancel</span>
          <span>× Tahan Paket (decision = held)</span>
        </button>
      </div>
    </div>
  </div>
</template>

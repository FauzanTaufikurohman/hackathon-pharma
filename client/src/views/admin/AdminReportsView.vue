<script setup lang="ts">
import { ref } from 'vue'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'
import AdminReportsHeader from '../../components/admin/AdminReportsHeader.vue'
import AdminComplianceReportCard from '../../components/admin/AdminComplianceReportCard.vue'
import AdminAuditEventsTable from '../../components/admin/AdminAuditEventsTable.vue'
import AdminFooter from '../../components/admin/AdminFooter.vue'
import {
  mockAdminDailyComplianceReport,
  mockAdminAuditEvents,
} from '../../data/mockShipments'

// Reactive state
const complianceReport = ref({ ...mockAdminDailyComplianceReport })
const auditEvents = ref([...mockAdminAuditEvents])

// Toast Notification
const toastMessage = ref<{ title: string; desc: string; isError?: boolean } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null

function showToast(title: string, desc: string, isError = false) {
  if (toastTimer) clearTimeout(toastTimer)
  toastMessage.value = { title, desc, isError }
  toastTimer = setTimeout(() => {
    toastMessage.value = null
  }, 4000)
}

// PDF Preview Modal
const isPdfModalOpen = ref(false)

function handleExportPdf() {
  isPdfModalOpen.value = true
}

// Distribute All Modal
const isDistributeModalOpen = ref(false)

function handleDistributeAll() {
  isDistributeModalOpen.value = true
}

function confirmDistribution() {
  isDistributeModalOpen.value = false
  showToast(
    'Distribusi Paralel Berhasil!',
    'Dokumen BAP & Laporan Harian CDOB telah disiarkan ke RS Medika Sehat, QA BioFarma, dan APJ Internal.'
  )
}
</script>

<template>
  <div class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <!-- Navbar Header -->
    <AdminNavbar active-tab="reports" />

    <!-- Main Content Container -->
    <main class="w-full max-w-7xl mx-auto px-6 pt-24 pb-8 flex-1 bg-surface">
      <div class="flex flex-col w-full">
        <!-- Top Meta / Section Header -->
        <AdminReportsHeader />

        <!-- Section 2: Top Daily Compliance Report Card -->
        <AdminComplianceReportCard
          :report="complianceReport"
          @export-pdf="handleExportPdf"
          @distribute-all="handleDistributeAll"
        />

        <AdminAuditEventsTable :events="auditEvents" />
      </div>
    </main>

    <!-- Footer -->
    <AdminFooter />

    <!-- MODAL: PDF PREVIEW & EXPORT -->
    <div
      v-if="isPdfModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="isPdfModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-surface-container flex flex-col gap-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-surface-container-low pb-3">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-2xl">picture_as_pdf</span>
            <div>
              <h3 class="font-headline-sm text-headline-sm text-on-surface">Dokumen Resmi Laporan Kepatuhan &amp; BAP</h3>
              <p class="font-body-sm text-body-sm text-on-surface-variant">CDOB-RPT-20261014-REV2 • Format PDF/A Standar BPOM</p>
            </div>
          </div>
          <button
            type="button"
            @click="isPdfModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <!-- Simulated PDF Document Layout -->
        <div class="bg-surface rounded-xl p-6 border border-surface-container font-mono text-xs flex flex-col gap-4">
          <div class="flex justify-between items-start border-b border-surface-container pb-4">
            <div>
              <div class="text-sm font-bold text-on-surface font-sans">ANTERAJA PHARMA COLD-CHAIN LOGISTICS</div>
              <div class="text-[11px] text-on-surface-variant font-sans">Laporan Audit Integritas Rantai Dingin Harian (CPOB)</div>
              <div class="text-[10px] text-on-surface-variant mt-1">Nomor Ledger: 0x7f8a9...b3c4 (SHA-256 Merkle Block)</div>
            </div>
            <div class="text-right">
              <span class="px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-bold text-[10px]">
                VALIDATED
              </span>
              <div class="text-[10px] text-on-surface-variant mt-1">14 Okt 2026, 15:30 WIB</div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 text-[11px]">
            <div>
              <div class="text-on-surface-variant">Pengesah Medis / APJ:</div>
              <div class="font-bold text-on-surface">dr. Ardiansyah, Apt.</div>
              <div class="text-on-surface-variant">SIP: 19820415/QA-PBF</div>
            </div>
            <div>
              <div class="text-on-surface-variant">Total Volume & Kepatuhan:</div>
              <div class="font-bold text-on-surface">18 Kargo • 98.8% Rentang 2-8°C</div>
              <div class="text-secondary font-bold">100% On-Schedule</div>
            </div>
          </div>

          <div class="border-t border-surface-container pt-3">
            <div class="text-[11px] font-bold text-primary mb-1">Berita Acara Penyimpangan Terkait (#ISSUE-8841):</div>
            <div class="text-[10px] text-on-surface bg-surface-container-low p-2.5 rounded-lg border border-surface-container font-sans">
              Resi <strong>PHA-260104</strong> (Vaksin Meningococcal): Segel fisik SL-8891 terdeteksi robek saat tiba di RS Medika Sehat. Otorisasi QA: Ditarik retur ke Hub Cawang untuk investigasi karantina.
            </div>
          </div>

          <div class="flex items-center justify-between border-t border-surface-container pt-3 text-[10px] text-on-surface-variant">
            <span>Tanda Tangan Digital RSA-2048: VALID</span>
            <span class="text-secondary font-bold">QR Verification Key Active</span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button
            type="button"
            @click="isPdfModalOpen = false"
            class="px-4 py-2 rounded-xl border border-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-low transition-colors"
          >
            Tutup
          </button>
          <button
            type="button"
            @click="isPdfModalOpen = false; showToast('Unduhan Berhasil!', 'Dokumen PDF/A terenkripsi telah disimpan ke folder Unduhan.')"
            class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md transition-all flex items-center gap-1.5 shadow-sm"
          >
            <span class="material-symbols-outlined text-lg">download</span>
            <span>Unduh File PDF Resmi</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: PARALLEL DISTRIBUTION CONFIRMATION -->
    <div
      v-if="isDistributeModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="isDistributeModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl border border-surface-container flex flex-col gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-primary-fixed text-primary rounded-xl flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">share</span>
          </div>
          <div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Distribusikan Laporan Resmi</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant">Kirim otomatis via Multi-Kanal Bot</p>
          </div>
        </div>

        <p class="font-body-md text-body-md text-on-surface">
          Dokumen Laporan Kepatuhan Harian &amp; BAP akan dikirimkan secara serentak ke pihak berkepentingan:
        </p>

        <ul class="space-y-2 text-label-sm text-on-surface bg-surface-container-low p-3.5 rounded-xl border border-surface-container">
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-base">check_circle</span>
            <span>Instalasi Farmasi RS Medika Sehat (Telegram Bot)</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-base">check_circle</span>
            <span>Quality Assurance PT BioFarma (Webhook API)</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-base">check_circle</span>
            <span>Kurir Bambang Suryono (WhatsApp Bot)</span>
          </li>
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-base">check_circle</span>
            <span>Apoteker Penanggung Jawab (Email PDF Terenkripsi)</span>
          </li>
        </ul>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button
            type="button"
            @click="isDistributeModalOpen = false"
            class="px-4 py-2 rounded-xl border border-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-low transition-colors"
          >
            Batal
          </button>
          <button
            type="button"
            @click="confirmDistribution"
            class="px-5 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md transition-all flex items-center gap-1.5 shadow-sm"
          >
            <span class="material-symbols-outlined text-lg">send</span>
            <span>Kirim Serentak Sekarang</span>
          </button>
        </div>
      </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toastMessage"
        class="fixed bottom-6 right-6 z-50 max-w-sm w-full bg-inverse-surface text-inverse-on-surface p-4 rounded-xl shadow-2xl border border-outline/30 flex items-start gap-3"
      >
        <span
          class="material-symbols-outlined text-xl shrink-0 mt-0.5"
          :class="toastMessage.isError ? 'text-error' : 'text-secondary-fixed-dim'"
        >
          {{ toastMessage.isError ? 'error' : 'verified' }}
        </span>
        <div class="flex-1">
          <div class="font-label-md text-label-md font-bold">{{ toastMessage.title }}</div>
          <div class="font-body-sm text-body-sm text-surface-variant mt-0.5 leading-snug">
            {{ toastMessage.desc }}
          </div>
        </div>
        <button
          type="button"
          @click="toastMessage = null"
          class="text-surface-variant hover:text-inverse-on-surface"
        >
          <span class="material-symbols-outlined text-sm">close</span>
        </button>
      </div>
    </transition>
  </div>
</template>

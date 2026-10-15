<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'
import AdminHeaderBanner from '../../components/admin/AdminHeaderBanner.vue'
import AdminMetricKpis from '../../components/admin/AdminMetricKpis.vue'
import AdminManifestRegistrationForm from '../../components/admin/AdminManifestRegistrationForm.vue'
import AdminShipmentsDirectoryTable from '../../components/admin/AdminShipmentsDirectoryTable.vue'
import AdminFooter from '../../components/admin/AdminFooter.vue'
import { mockAdminShipments } from '../../data/mockShipments'
import type { AdminShipmentItem } from '../../types/shipment'

const router = useRouter()

// Shipments reactive data
const shipments = ref<AdminShipmentItem[]>([...mockAdminShipments])

// Interactive Toast Notification
const toastMessage = ref<string | null>(null)
const toastType = ref<'success' | 'info' | 'warning'>('success')
let toastTimer: ReturnType<typeof setTimeout> | null = null

function showToast(message: string, type: 'success' | 'info' | 'warning' = 'success') {
  if (toastTimer) clearTimeout(toastTimer)
  toastMessage.value = message
  toastType.value = type
  toastTimer = setTimeout(() => {
    toastMessage.value = null
  }, 4000)
}

// Modal States
const isTicketModalOpen = ref(false)
const selectedTicketAwb = ref('')

const isPodModalOpen = ref(false)
const selectedPodAwb = ref('')

const isGpsModalOpen = ref(false)
const selectedGpsAwb = ref('')

// Handler for registration form submission
function handleCreateShipment(formData: {
  awb: string
  sender_name: string
  origin_city: string
  recipient_name: string
  recipient_phone: string
  address: string
  item_summary: string
  koli_count: string
  temp_profile: 'chilled' | 'ambient'
  temp_min: number
  temp_max: number
  handling_instructions: string
  inspector_name: string
  scheduled_at: string
}) {
  const newShipment: AdminShipmentItem = {
    id: Date.now(),
    awb: formData.awb,
    sender_origin: `${formData.sender_name} (${formData.origin_city})`,
    recipient_name: formData.recipient_name,
    recipient_city: formData.address.length > 20 ? formData.address.substring(0, 20) + '...' : formData.address,
    commodity: formData.item_summary,
    koli: formData.koli_count,
    temp_profile: `${formData.temp_min.toFixed(1)}°C - ${formData.temp_max.toFixed(1)}°C`,
    temp_type: formData.temp_profile,
    officer_name: formData.inspector_name.split(' (')[0] || formData.inspector_name,
    officer_role: 'Checker Hub',
    status: 'inspection',
    action_label: 'Detail',
    action_type: 'detail',
  }

  // Insert to the top of list
  shipments.value.unshift(newShipment)
  showToast(`Manifest kargo ${formData.awb} berhasil diterbitkan & status diarahkan ke inspection!`, 'success')
}

// Export CSV handler
function handleExportCsv() {
  const headers = 'AWB,Pengirim,Penerima,Tujuan,Komoditas,Koli,Suhu,Petugas,Status\n'
  const rows = shipments.value
    .map(
      (s) =>
        `"${s.awb}","${s.sender_origin}","${s.recipient_name}","${s.recipient_city}","${s.commodity}","${s.koli}","${s.temp_profile}","${s.officer_name}","${s.status}"`
    )
    .join('\n')

  const blob = new Blob([headers + rows], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `Manifest_Pharma_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)

  showToast('Manifest .CSV berhasil diunduh secara lokal!', 'success')
}

// Print Barcode handler
function handlePrintBarcode() {
  window.print()
}

// Action handlers from table
function handleReviewQc(awb: string) {
  showToast(`Membuka Review QC untuk resi ${awb}...`, 'info')
  router.push('/admin/approval-qc-kurir')
}

function handleMonitorGps(awb: string) {
  selectedGpsAwb.value = awb
  isGpsModalOpen.value = true
}

function handleOpenTicket(awb: string) {
  selectedTicketAwb.value = awb
  isTicketModalOpen.value = true
}

function handleViewDetail(awb: string) {
  showToast(`Detail resi kargo ${awb} dimuat.`, 'info')
}

function handleViewEpod(awb: string) {
  selectedPodAwb.value = awb
  isPodModalOpen.value = true
}
</script>

<template>
  <div class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <!-- Navbar Header -->
    <AdminNavbar active-tab="manajemen-pengiriman" />

    <!-- Main Content Container -->
    <main class="w-full max-w-7xl mx-auto px-6 py-8 flex-1 bg-surface">
      <div class="flex flex-col w-full">
        <!-- Top Section Header -->
        <AdminHeaderBanner
          @export-csv="handleExportCsv"
          @print-barcode="handlePrintBarcode"
        />

        <!-- Top Summary Row: 4 Metric Cards -->
        <AdminMetricKpis />

        <!-- Main Split Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-lg items-start">
          <!-- Left Column: Form Pendaftaran (5 cols) -->
          <div class="lg:col-span-5">
            <AdminManifestRegistrationForm @submit="handleCreateShipment" />
          </div>

          <!-- Right Column: Direktori Pengiriman Aktif (7 cols) -->
          <div class="lg:col-span-7">
            <AdminShipmentsDirectoryTable
              :shipments="shipments"
              @review-qc="handleReviewQc"
              @monitor-gps="handleMonitorGps"
              @open-ticket="handleOpenTicket"
              @view-detail="handleViewDetail"
              @view-epod="handleViewEpod"
            />
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <AdminFooter />

    <!-- Toast Notification -->
    <Transition
      enter-active-class="transform transition ease-out duration-300"
      enter-from-class="translate-y-4 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toastMessage"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-lg border"
        :class="[
          toastType === 'success' ? 'bg-secondary text-on-secondary border-secondary-container' : '',
          toastType === 'info' ? 'bg-inverse-surface text-inverse-on-surface border-surface-container-high' : '',
          toastType === 'warning' ? 'bg-tertiary-container text-on-tertiary-container border-tertiary' : '',
        ]"
      >
        <span class="material-symbols-outlined text-xl">
          {{ toastType === 'success' ? 'check_circle' : toastType === 'info' ? 'info' : 'warning' }}
        </span>
        <span class="font-label-md text-label-md">{{ toastMessage }}</span>
        <button
          type="button"
          @click="toastMessage = null"
          class="ml-2 hover:opacity-75 transition-opacity"
        >
          <span class="material-symbols-outlined text-sm">close</span>
        </button>
      </div>
    </Transition>

    <!-- GPS Quick Modal -->
    <div
      v-if="isGpsModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="isGpsModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-surface-container">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container-low mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-2xl">gps_fixed</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Pelacakan Satria GPS ({{ selectedGpsAwb }})</h3>
          </div>
          <button
            type="button"
            @click="isGpsModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="space-y-4">
          <div class="bg-surface-container-low p-4 rounded-xl flex items-center justify-between">
            <div>
              <span class="font-label-sm text-label-sm text-on-surface-variant block">Armada Satria</span>
              <span class="font-headline-sm text-headline-sm text-on-surface">Bambang Suryono (KURIR-007)</span>
              <span class="font-body-sm text-body-sm text-secondary block mt-0.5">Motor Box Cold-Chain • B 4819 KFX</span>
            </div>
            <div class="text-right">
              <span class="font-label-sm text-label-sm text-on-surface-variant block">Probe IoT</span>
              <span class="font-headline-sm text-headline-sm text-secondary font-bold">4.2°C</span>
              <span class="font-label-sm text-label-sm text-secondary block">Aman 2-8°C</span>
            </div>
          </div>

          <div class="bg-surface-container p-4 rounded-xl space-y-2">
            <div class="flex items-center justify-between font-label-md text-label-md">
              <span class="text-on-surface-variant">Posisi Terkini:</span>
              <span class="text-on-surface font-semibold">Tol Dalam Kota KM 8 (Pancoran)</span>
            </div>
            <div class="flex items-center justify-between font-label-md text-label-md">
              <span class="text-on-surface-variant">Estimasi Tiba:</span>
              <span class="text-secondary font-bold">~14 Menit (14:45 WIB)</span>
            </div>
            <div class="flex items-center justify-between font-label-md text-label-md">
              <span class="text-on-surface-variant">Status IoT Gateway:</span>
              <span class="text-on-surface flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                BLE Active (98% Bat)
              </span>
            </div>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button
              type="button"
              @click="isGpsModalOpen = false"
              class="px-4 py-2 rounded-lg bg-surface-container-low text-on-surface font-label-md hover:bg-surface-container"
            >
              Tutup
            </button>
            <button
              type="button"
              @click="isGpsModalOpen = false; router.push('/admin/live-monitor-kendala')"
              class="px-4 py-2 rounded-lg bg-primary text-on-primary font-label-md hover:bg-on-primary-fixed-variant"
            >
              Buka Radar Lengkap
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ticket Incident Modal -->
    <div
      v-if="isTicketModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="isTicketModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-error/30">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container-low mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-error text-2xl">warning</span>
            <h3 class="font-headline-sm text-headline-sm text-error">Tiket Kendala &amp; Karantina ({{ selectedTicketAwb }})</h3>
          </div>
          <button
            type="button"
            @click="isTicketModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="space-y-4">
          <div class="bg-error-container/40 p-4 rounded-xl border border-error/20">
            <span class="font-label-sm text-label-sm text-error font-bold uppercase tracking-wider block mb-1">
              Status Resi Ditahan (held)
            </span>
            <p class="font-body-md text-body-md text-on-surface">
              Kargo ditahan karena segel tamper terindikasi rusak dan suhu terbaca <strong>8.9°C</strong> (melebihi batas aman CPOB 8.0°C).
            </p>
          </div>

          <div class="bg-surface-container-low p-4 rounded-xl space-y-2 text-on-surface font-body-sm text-body-sm">
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Komoditas:</span>
              <span class="font-semibold">Serum Anti-Bisa Ular (1 Koli)</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Lokasi Penyimpanan:</span>
              <span class="font-semibold">Ruang Karantina Cold-Room Hub Cawang #02</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Checker Pelapor:</span>
              <span>Hendra Wijaya (CHK-012)</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Status Investigasi:</span>
              <span class="text-error font-bold">Investigasi QA Terbuka</span>
            </div>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button
              type="button"
              @click="isTicketModalOpen = false"
              class="px-4 py-2 rounded-lg bg-surface-container-low text-on-surface font-label-md hover:bg-surface-container"
            >
              Tutup
            </button>
            <button
              type="button"
              @click="isTicketModalOpen = false; showToast('Disposisi Berita Acara Karantina dicatat.', 'success')"
              class="px-4 py-2 rounded-lg bg-error text-on-error font-label-md hover:bg-on-error-container"
            >
              Unduh BA Karantina
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- e-POD Modal -->
    <div
      v-if="isPodModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="isPodModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-surface-container">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container-low mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-2xl">verified</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Dokumen Elektronik Proof of Delivery</h3>
          </div>
          <button
            type="button"
            @click="isPodModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="space-y-4">
          <div class="bg-surface-container-low p-4 rounded-xl space-y-2">
            <div class="flex justify-between">
              <span class="text-on-surface-variant font-label-md text-label-md">Nomor Resi:</span>
              <span class="font-headline-sm text-headline-sm text-primary font-bold">{{ selectedPodAwb }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant font-label-md text-label-md">Fasilitas Penerima:</span>
              <span class="font-body-md text-body-md text-on-surface font-semibold">Apotek K-24 Tebet Raya</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant font-label-md text-label-md">Penerima Berwenang:</span>
              <span class="font-body-md text-body-md text-on-surface">Apt. Sarah Nadia, S.Farm (SIP: 1988/IV/2021)</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant font-label-md text-label-md">Waktu Serah Terima:</span>
              <span class="font-body-md text-body-md text-secondary font-semibold">14 Okt 2026, 11:20 WIB</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant font-label-md text-label-md">Validasi Temperatur:</span>
              <span class="font-body-md text-body-md text-secondary font-semibold">18.2°C (Ambient 15-25°C)</span>
            </div>
          </div>

          <div class="border border-dashed border-outline-variant p-4 rounded-xl text-center">
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase block mb-2">Tanda Tangan Digital Terverifikasi</span>
            <div class="inline-block px-4 py-2 bg-surface-container-low rounded font-serif italic text-lg text-on-surface">
              Sarah Nadia, S.Farm
            </div>
            <span class="block text-xs text-secondary font-semibold mt-1">Hash SHA-256: 7f8b91a2c0... [Valid]</span>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button
              type="button"
              @click="isPodModalOpen = false"
              class="px-4 py-2 rounded-lg bg-surface-container-low text-on-surface font-label-md hover:bg-surface-container"
            >
              Tutup
            </button>
            <button
              type="button"
              @click="isPodModalOpen = false; showToast('Dokumen e-POD PDF berhasil diunduh.', 'success')"
              class="px-4 py-2 rounded-lg bg-primary text-on-primary font-label-md hover:bg-on-primary-fixed-variant"
            >
              Unduh e-POD (PDF)
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

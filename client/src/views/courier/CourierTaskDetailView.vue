<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mockCourierTasks } from '../../data/mockShipments'
import type { CourierTaskItem } from '../../types/shipment'
import CourierTaskDetailNavbar from '../../components/courier/CourierTaskDetailNavbar.vue'

// Tab 1: Tugas Masuk Components
import CourierIncomingUrgencyBanner from '../../components/courier/CourierIncomingUrgencyBanner.vue'
import CourierIncomingCardHeader from '../../components/courier/CourierIncomingCardHeader.vue'
import CourierIncomingTelemetryGrid from '../../components/courier/CourierIncomingTelemetryGrid.vue'
import CourierIncomingRouteCol from '../../components/courier/CourierIncomingRouteCol.vue'
import CourierIncomingSpecsCol from '../../components/courier/CourierIncomingSpecsCol.vue'
import CourierIncomingPreTripChecklist from '../../components/courier/CourierIncomingPreTripChecklist.vue'
import CourierIncomingActionFooter from '../../components/courier/CourierIncomingActionFooter.vue'
import CourierIncomingRejectModal from '../../components/courier/CourierIncomingRejectModal.vue'
import CourierIncomingSuccessBanner from '../../components/courier/CourierIncomingSuccessBanner.vue'

// Tab 2: Pengantaran & Monitor Components
import CourierDeliveryStepper from '../../components/courier/CourierDeliveryStepper.vue'
import CourierDeliveryMapCard from '../../components/courier/CourierDeliveryMapCard.vue'
import CourierDeliveryRecipientCard from '../../components/courier/CourierDeliveryRecipientCard.vue'
import CourierDeliveryTelemetryPanel from '../../components/courier/CourierDeliveryTelemetryPanel.vue'
import CourierDeliveryIssueBanner from '../../components/courier/CourierDeliveryIssueBanner.vue'

// Tab 3: Lapor Kendala Components
import CourierIssueNoticeBanner from '../../components/courier/CourierIssueNoticeBanner.vue'
import CourierIssueReportForm from '../../components/courier/CourierIssueReportForm.vue'
import CourierIssueDecisionCard from '../../components/courier/CourierIssueDecisionCard.vue'
import CourierIssueAuditLogStrip from '../../components/courier/CourierIssueAuditLogStrip.vue'

// Tab 4: Serah Terima & Laporan Components
import CourierHandoverBanner from '../../components/courier/CourierHandoverBanner.vue'
import CourierHandoverEpodCard from '../../components/courier/CourierHandoverEpodCard.vue'
import CourierHandoverCargoSummaryCard from '../../components/courier/CourierHandoverCargoSummaryCard.vue'
import CourierHandoverTimelineCard from '../../components/courier/CourierHandoverTimelineCard.vue'
import CourierHandoverReportCard from '../../components/courier/CourierHandoverReportCard.vue'

import CourierFooter from '../../components/courier/CourierFooter.vue'
import NotificationToast from '../../components/vendor/NotificationToast.vue'

const route = useRoute()
const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

// Current AWB from URL or default to PHA-260104
const awbParam = computed(() => (route.params.awb as string) || 'PHA-260104')

// Task lookup
const task = computed<CourierTaskItem>(() => {
  const match = mockCourierTasks.find(
    t => t.awb.toLowerCase() === awbParam.value.toLowerCase()
  )
  return match || mockCourierTasks[0]!
})

// Active Tab (1: Tugas Masuk, 2: Pengantaran & Monitor, 3: Lapor Kendala, 4: Serah Terima & Laporan)
const activeTab = ref<number>(Number(route.query.tab) || 1)

// Synchronize tab with route query
watch(
  () => route.query.tab,
  newTab => {
    if (newTab) {
      activeTab.value = Number(newTab)
    }
  }
)

// Tab 1: Checklist & Action state
const check1 = ref(false)
const check2 = ref(false)
const check3 = ref(false)
const isProcessing = ref(false)
const isAccepted = ref(task.value.status === 'in_transit' || task.value.status === 'delivered')
const isRejectModalOpen = ref(false)

// Tab 2: Delivery & Arrival state
const hasArrived = ref(false)
const arrivedTime = ref('14:50')

function handleTabChange(tabNumber: number) {
  activeTab.value = tabNumber
  router.replace({
    query: { ...route.query, tab: tabNumber.toString() },
  })

  const tabLabels: Record<number, string> = {
    1: 'Tugas Masuk',
    2: 'Pengantaran & Monitor',
    3: 'Lapor Kendala',
    4: 'Serah Terima & Laporan',
  }

  const tabName = tabLabels[tabNumber] || 'Tugas Masuk'
  toastRef.value?.show(
    `Navigasi Satria: ${tabName}`,
    `Beralih ke tampilan ${tabName} untuk kargo ${task.value.awb}...`
  )
}

// Tab 1 Actions
function handleAcceptTask() {
  const allChecked = check1.value && check2.value && check3.value
  if (!allChecked) {
    const proceed = window.confirm(
      'Perhatian: Anda belum mencentang seluruh 3 poin Verifikasi Pra-Jalan. Tetap lanjutkan serah terima fisik?'
    )
    if (!proceed) return
  }

  isProcessing.value = true

  setTimeout(() => {
    isProcessing.value = false
    isAccepted.value = true
    task.value.status = 'in_transit'
    task.value.status_label = 'Dalam Perjalanan (in_transit)'

    toastRef.value?.show(
      'Tugas Berhasil Diterima!',
      `Resi ${task.value.awb} telah tersinkron ke Pengantaran & Monitor. Silakan mulai navigasi.`
    )
  }, 700)
}

function handleRejectClick() {
  isRejectModalOpen.value = true
}

function handleRejectSubmit(payload: { category: string; notes: string }) {
  isRejectModalOpen.value = false
  task.value.status = 'hold'
  toastRef.value?.show(
    'Tugas Ditolak (Exemption)',
    `Penolakan kargo ${task.value.awb} berhasil dicatat. Tugas dialihkan ke Satria cadangan di Hub Cawang.`
  )
  setTimeout(() => {
    router.push('/courier/tugas-saya')
  }, 1200)
}

function handleNavigateMonitor() {
  handleTabChange(2)
}

// Tab 2 Actions
function handleConfirmArrived() {
  hasArrived.value = true
  arrivedTime.value = new Date().toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
  toastRef.value?.show(
    'Status Diperbarui: ARRIVED',
    `Kendaraan Satria telah tiba di lokasi ${task.value.destination_name || 'RS Medika Sehat Jakarta'}. Siap lanjut ke serah terima fisik.`
  )
}

function handleGoHandover() {
  handleTabChange(4)
}

function handleOpenIssueForm() {
  handleTabChange(3)
}

// Tab 3 Actions
const isHeld = ref(true)
const isReturning = ref(false)

function handleIssueSubmit(payload: { category: string; description: string }) {
  isHeld.value = true
  task.value.status = 'hold'
  task.value.status_label = 'Ditahan (HELD)'
  toastRef.value?.show(
    'Laporan Kendala Terkirim!',
    `AWB ${task.value.awb} berhasil dikunci ke status HELD. Menunggu instruksi resmi Admin QA.`
  )
}

function handleConfirmReturn() {
  isReturning.value = true
  task.value.status = 'hold'
  toastRef.value?.show(
    'Instruksi Retur Dikonfirmasi!',
    `Status pengiriman diperbarui menjadi returning. Rute navigasi diarahkan kembali ke Hub Cawang.`
  )
}

// Tab 4 Actions
function handleDownloadBapPdf() {
  toastRef.value?.show(
    'Mengunduh Salinan Dokumen BAP',
    `Memproses file BAP-PHA-20261014-8891.pdf terenkripsi SHA-256 untuk resi ${task.value.awb}...`
  )
}

function handleShareWhatsappDispatcher() {
  const text = encodeURIComponent(
    `Laporan Tugas Selesai (DELIVERED):\nAWB: ${task.value.awb}\nPenerima: Apt. Siti Rahma, S.Farm (RS Medika Sehat Jakarta)\nSuhu Serah Terima: 4.4°C\nSegel Fisik: SL-8891 (Utuh)\nWaktu Selesai: 14 Okt 2026, 15:18 WIB\ne-POD: BAP-PHA-20261014-8891 (Valid CDOB BPOM)`
  )
  window.open(`https://wa.me/6281288889999?text=${text}`, '_blank')
}
</script>

<template>
  <div class="bg-surface font-body-md text-on-surface antialiased min-h-screen flex flex-col justify-between">
    <!-- Fixed Top Navbar with 4 Execution Tabs -->
    <CourierTaskDetailNavbar
      :active-tab="activeTab"
      :awb="task.awb"
      @tab-change="handleTabChange"
    />

    <!-- Main Workspace Content -->
    <main class="w-full pt-16 flex-1 bg-surface">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <!-- TAB 1: TUGAS MASUK -->
        <div v-if="activeTab === 1" class="flex flex-col w-full space-y-6">
          <!-- 1. Top Excursion / Urgency Countdown Banner -->
          <CourierIncomingUrgencyBanner
            :hub-location="task.origin_detail || 'Hub Cawang Staging #02'"
          />

          <!-- 2. Main Assignment Card (FR-03 Cold Chain Task Confirmation) -->
          <div class="bg-surface-container-lowest rounded-xl shadow-md overflow-hidden border border-surface-container/60">
            <!-- Header Block -->
            <CourierIncomingCardHeader :task="task" />

            <!-- Telemetry & Visual Preview 4-Card Row -->
            <CourierIncomingTelemetryGrid :task="task" />

            <!-- 2-Column Detail Grid: Route & Specs -->
            <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-2 gap-6 bg-surface">
              <!-- Col 1: Rute & Lokasi Pengiriman -->
              <CourierIncomingRouteCol :task="task" />

              <!-- Col 2: Spesifikasi Muatan & CPOB -->
              <CourierIncomingSpecsCol :task="task" />
            </div>

            <!-- Pre-Trip Preparation Checklist (Interactive Inspection) -->
            <CourierIncomingPreTripChecklist
              v-model:check1="check1"
              v-model:check2="check2"
              v-model:check3="check3"
            />

            <!-- Ergonomic Large Action Footer -->
            <CourierIncomingActionFooter
              :is-processing="isProcessing"
              :is-accepted="isAccepted"
              @reject="handleRejectClick"
              @accept="handleAcceptTask"
            />
          </div>

          <!-- 3. Success Notification Sheet (When Accepted) -->
          <CourierIncomingSuccessBanner
            v-if="isAccepted"
            :awb="task.awb"
            @navigate-monitor="handleNavigateMonitor"
          />
        </div>

        <!-- TAB 2: PENGANTARAN & MONITOR -->
        <div v-else-if="activeTab === 2" class="flex flex-col w-full space-y-6">
          <!-- Sequential Control Card: Changing Status & 3-Step Flow -->
          <CourierDeliveryStepper
            :task="task"
            :has-arrived="hasArrived"
            :arrived-time="arrivedTime"
            @confirm-arrived="handleConfirmArrived"
            @go-handover="handleGoHandover"
          />

          <!-- Split Main Layout: 2/3 Map & Navigation + 1/3 Telemetry Monitoring -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Navigation View & Contact Details -->
            <div class="lg:col-span-2 space-y-6">
              <!-- Active Route Map Visualizer -->
              <CourierDeliveryMapCard
                speed="28.4 km/h"
                distance-remaining="4.2 KM"
                current-position="Tol Cawang KM 12 → Exit Kuningan/Salemba"
                traffic-status="Trafik: Lancar Terkendali"
                :destination-query="task.destination_name || 'RS Medika Sehat Jakarta Salemba'"
              />

              <!-- Recipient Contact & Pharmacy Card -->
              <CourierDeliveryRecipientCard
                :facility-name="task.destination_name || 'RS Medika Sehat Jakarta'"
                :facility-address="task.destination_detail || 'Gedung Farmasi & Logistik Medik, Lantai 1 (Pintu Masuk Loading Dock Barat)'"
                apj-name="Apt. Siti Rahma, S.Farm"
                apj-sip="SIP: 19880412/SIPA-DKI/2022"
                api-phone="0812-9842-1100"
              />
            </div>

            <!-- Right Column: Running Monitor Telemetry Panel & Issue Banner -->
            <div class="space-y-6">
              <!-- Live Telemetry Monitor Card -->
              <CourierDeliveryTelemetryPanel
                :current-temp="4.5"
                probe-id="SENS-IOT-771"
                :battery-pct="96"
                protocol="BLE 5.2 / CPOB"
                ping-cycle="Tiap 30 Detik"
              />

              <!-- Field Issue Shortcut Banner -->
              <CourierDeliveryIssueBanner
                @open-issue-form="handleOpenIssueForm"
              />
            </div>
          </div>
        </div>

        <!-- TAB 3: LAPOR KENDALA -->
        <div v-else-if="activeTab === 3" class="flex flex-col w-full space-y-6">
          <!-- Top Notice Banner: Cold-Chain BPOM Protocol Alert -->
          <CourierIssueNoticeBanner />

          <!-- Main Content Split Grid: 2 Columns -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Left Card: Form Lapor Kendala Baru (issues table) -->
            <CourierIssueReportForm
              :awb="task.awb"
              :is-held="isHeld"
              @submit-issue="handleIssueSubmit"
            />

            <!-- Right Card: Status Penahanan & Keputusan Admin (decisions table) -->
            <CourierIssueDecisionCard
              :awb="task.awb"
              :is-returning="isReturning"
              @confirm-return="handleConfirmReturn"
            />
          </div>

          <!-- Bottom: Active Investigation Log / Audit Trail Strip -->
          <CourierIssueAuditLogStrip />
        </div>

        <!-- TAB 4: SERAH TERIMA & LAPORAN -->
        <div v-else-if="activeTab === 4" class="flex flex-col w-full space-y-6">
          <!-- Top Confirmation Banner -->
          <CourierHandoverBanner
            :awb="task.awb"
            completion-time="14 Okt 2026, 15:18 WIB"
          />

          <!-- Main Split 2-Column Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Left Column: e-POD Validation Form -->
            <CourierHandoverEpodCard
              :awb="task.awb"
              completion-time="15:18 WIB"
              :temp-reading="4.4"
              seal-code="SL-8891"
              pharmacist-name="Apt. Siti Rahma, S.Farm"
              pharmacist-nipa="19890412/APJ-RSMS"
              :facility-name="task.destination_name || 'RS Medika Sehat Jakarta'"
            />

            <!-- Right Column: Riwayat Tugas & Dokumen Laporan -->
            <div class="space-y-6">
              <!-- Cargo Summary Card -->
              <CourierHandoverCargoSummaryCard
                :cargo-name="task.commodity_name || 'Vaksin Meningococcal Konjugat ACYW'"
                :batch-number="task.batch_number || '#MCV-2026-X12'"
                expiry-date="Nov 2027"
                colli-count="2 Koli Terverifikasi"
                :origin-hub="task.origin_name || 'Hub Pharma Cawang (DC)'"
                :destination-facility="task.destination_name || 'RS Medika Sehat Jakarta'"
              />

              <!-- Mini Audit Log Ledger (Chain of Custody) -->
              <CourierHandoverTimelineCard />

              <!-- Official Report Download Card -->
              <CourierHandoverReportCard
                bap-number="BAP-PHA-20261014-8891"
                pdf-size="1.8 MB"
                @download-pdf="handleDownloadBapPdf"
                @share-whatsapp="handleShareWhatsappDispatcher"
              />
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Rejection Modal Dialog (Tab 1) -->
    <CourierIncomingRejectModal
      :is-open="isRejectModalOpen"
      :awb="task.awb"
      @close="isRejectModalOpen = false"
      @submit="handleRejectSubmit"
    />

    <!-- Global Toast Notifications -->
    <NotificationToast ref="toastRef" />

    <!-- Courier Footer -->
    <CourierFooter />
  </div>
</template>

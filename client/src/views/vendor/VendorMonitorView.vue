<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Shipment } from '../../types/shipment'
import { mockShipments } from '../../data/mockShipments'
import MonitorTabNav from '../../components/monitor/MonitorTabNav.vue'
import MonitorSubHeader from '../../components/monitor/MonitorSubHeader.vue'
import ShipmentOverviewCard from '../../components/monitor/ShipmentOverviewCard.vue'
import LifecycleStepper from '../../components/monitor/LifecycleStepper.vue'
import WarehouseInspectionCard from '../../components/monitor/WarehouseInspectionCard.vue'
import TelemetrySubHeader from '../../components/telemetry/TelemetrySubHeader.vue'
import LiveLocationRibbon from '../../components/telemetry/LiveLocationRibbon.vue'
import LiveGpsMapCard from '../../components/telemetry/LiveGpsMapCard.vue'
import CourierCard from '../../components/telemetry/CourierCard.vue'
import TemperatureTelemetryChart from '../../components/telemetry/TemperatureTelemetryChart.vue'
import FieldIssueCard from '../../components/telemetry/FieldIssueCard.vue'
import DeliveryHandoverFooter from '../../components/telemetry/DeliveryHandoverFooter.vue'
import NotificationToast from '../../components/vendor/NotificationToast.vue'

const route = useRoute()
const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

const awb = computed(() => (route.params.awb as string) || 'PHA-260104')
const activeTab = ref<number>(Number(route.query.screen) || 1)

// Find matching shipment or fallback to PHA-260104
const shipment = computed<Shipment>(() => {
  const decodedAwb = decodeURIComponent(awb.value)
  return (
    mockShipments.find(s => s.tracking_number.toLowerCase() === decodedAwb.toLowerCase()) ||
    mockShipments[0]!
  )
})

function handleTabChange(tab: number) {
  activeTab.value = tab
  router.replace({
    query: { screen: tab.toString() },
  })

  const tabNames: Record<number, string> = {
    1: 'Monitor Pengiriman (Status Kesiapan & Pemeriksaan Awal)',
    2: 'Lacak Kiriman & Suhu (Sensor IoT & Rute GPS Live)',
    3: 'Laporan & Riwayat Audit (Dokumen BAP & CPOB)',
  }
  const tabTitle = tabNames[tab] || 'Monitor Pengiriman'
  toastRef.value?.show(tabTitle, `Memuat data navigasi untuk resi ${shipment.value.tracking_number}...`)
}

function handleToggleReadiness(isReady: boolean) {
  shipment.value.recipient_ready = isReady
  if (isReady) {
    toastRef.value?.show(
      'Kesiapan Faskes Dikonfirmasi!',
      `Fasilitas ${shipment.value.recipient_name} siap menerima kargo (recipient_ready = true).`
    )
  } else {
    toastRef.value?.show(
      'Kesiapan Dibatalkan',
      'Status kesiapan fasilitas dikembalikan ke menunggu konfirmasi.'
    )
  }
}

function handleCopyAwb() {
  toastRef.value?.show(
    'No. Resi Disalin!',
    `Nomor AWB ${shipment.value.tracking_number} berhasil disalin ke clipboard.`
  )
}

function handleDownloadLogPdf() {
  toastRef.value?.show(
    'Mengunduh Data Log Suhu (.PDF)',
    'Laporan PDF data suhu sedang diunduh sesuai standar CDOB BPOM.'
  )
}
</script>

<template>
  <div class="w-full flex flex-col pb-space-xl">
    <!-- 3-Tab Sub-Navigation Header -->
    <MonitorTabNav
      :active-tab="activeTab"
      :awb-number="shipment.tracking_number"
      @update:active-tab="handleTabChange"
    />

    <!-- TAB 1: Monitor Pengiriman (Status Kesiapan & Pemeriksaan Awal) -->
    <div v-if="activeTab === 1" class="space-y-6">
      <MonitorSubHeader />

      <!-- Card 1: Shipment Overview (shipments table) -->
      <ShipmentOverviewCard
        :shipment="shipment"
        @toggle-readiness="handleToggleReadiness"
      />

      <!-- Card 2: Status Lifecycle Stepper (5 Stages CPOB & GDP) -->
      <LifecycleStepper :current-status="shipment.status" />

      <!-- Card 3: Warehouse Physical & Thermal Inspection (inspections table) -->
      <WarehouseInspectionCard :inspection="shipment.inspection" />
    </div>

    <!-- TAB 2: Lacak Kiriman & Suhu (Sensor IoT & Rute GPS Live) -->
    <div v-else-if="activeTab === 2" class="flex flex-col w-full">
      <!-- Navigation Sub-header & Breadcrumb Meta -->
      <TelemetrySubHeader
        :shipment="shipment"
        @copy-awb="handleCopyAwb"
      />

      <!-- Live Location Ribbon -->
      <LiveLocationRibbon
        :location-text="shipment.last_location || 'Tol Dalam Kota - Cawang (Menuju RS Medika Sehat, Kuningan)'"
        updated-ago="Baru saja"
      />

      <!-- 2-Column Responsive Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: 2/3 Width - Live Map & Courier Assignment -->
        <div class="lg:col-span-2 flex flex-col gap-6">
          <!-- Interactive GPS Map Card -->
          <LiveGpsMapCard
            :origin-name="shipment.sender_name"
            origin-time="Pukul 11:20 WIB"
            last-gps-point="KM 02 Tol Cawang"
            last-gps-time="Pukul 15:06 WIB"
            :destination-name="shipment.recipient_name"
            destination-eta="Est. 15:30 WIB"
            remaining-distance="8.4 KM"
            reefer-plate="Reefer Satria B 9482 PBF"
            average-temp="Stabil 4.5°C"
          />

          <!-- Courier Assignment Bottom Card -->
          <CourierCard
            courier-name="Bambang Suryono"
            courier-id="STR-PHR-094"
            fleet-plate="Reefer Dedicated Box B 9482 PBF"
            status-text="Satria siap mengantar / Aktif di rute"
            reefer-range="2.0°C – 8.0°C Aktif"
            scheduled-at="14 Okt 2026, 15:30 WIB"
            :phone-url="`https://wa.me/6281234567890?text=Halo%20Satria%20Bambang,%20konfirmasi%20kiriman%20${encodeURIComponent(shipment.item_summary)}%20RS%20Medika%20Sehat%20AWB%20${shipment.tracking_number}`"
          />
        </div>

        <!-- Right Column: 1/3 Width - Temperature Telemetry & Field Issue Monitor -->
        <div class="flex flex-col gap-6">
          <!-- Card A: Suhu Kargo Saat Ini (Sensor IoT) -->
          <TemperatureTelemetryChart
            sensor-id="SNS-CO-8821"
            :current-temp="shipment.current_temperature || 4.5"
            :target-temp="4.0"
            :battery-pct="94"
            calibration-expiry="Aktif s/d 2027"
          />

          <!-- Card B: Status Kendala Lapangan (Issues & Decisions) -->
          <FieldIssueCard
            :active-issues-count="0"
            qa-on-duty="dr. Ardiansyah (Apoteker QA)"
          />
        </div>
      </div>

      <!-- Medical Safety Note Footer for Apoteker / Buyer -->
      <DeliveryHandoverFooter
        @download-log-pdf="handleDownloadLogPdf"
      />
    </div>

    <!-- TAB 3: Laporan & Riwayat Audit (Dokumen BAP & CPOB) Placeholder -->
    <div v-else-if="activeTab === 3" class="space-y-6">
      <div class="bg-surface-container-lowest rounded-2xl p-8 border border-surface-container shadow-xs text-center flex flex-col items-center justify-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-primary-fixed flex items-center justify-center text-primary shadow-xs">
          <span class="material-symbols-outlined text-[36px]">verified</span>
        </div>
        <div class="max-w-md">
          <h2 class="font-headline-md text-headline-md font-extrabold text-on-surface">
            Laporan &amp; Riwayat Audit Digital
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Arsip dokumen Berita Acara Penerimaan (BAP) PDF/A berstandar CPOB &amp; GDP terenkripsi dengan hash verifikasi.
          </p>
        </div>
        <button
          type="button"
          class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md font-bold transition-all shadow-xs flex items-center gap-2 cursor-pointer"
        >
          <span class="material-symbols-outlined text-[18px]">download</span>
          <span>Unduh BAP Resmi Resi {{ shipment.tracking_number }}</span>
        </button>
      </div>
    </div>

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

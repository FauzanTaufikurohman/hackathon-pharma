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
</script>

<template>
  <div class="w-full flex flex-col pb-space-xl">
    <!-- Sub-header & Navigation Context -->
    <MonitorSubHeader />

    <!-- 3-Tab Navigation Bar -->
    <MonitorTabNav
      :active-tab="activeTab"
      :awb-number="shipment.tracking_number"
      @update:active-tab="handleTabChange"
    />

    <!-- TAB 1: Monitor Pengiriman (Main Active Section) -->
    <div v-if="activeTab === 1" class="space-y-6">
      <!-- Card 1: Shipment Overview (shipments table) with 4 Columns & Interactive Readiness Banner -->
      <ShipmentOverviewCard
        :shipment="shipment"
        @toggle-readiness="handleToggleReadiness"
      />

      <!-- Card 2: Status Lifecycle Stepper (5 Stages CPOB & GDP) -->
      <LifecycleStepper :current-status="shipment.status" />

      <!-- Card 3: Warehouse Physical & Thermal Inspection (inspections table) -->
      <WarehouseInspectionCard :inspection="shipment.inspection" />
    </div>

    <!-- TAB 2: Lacak Kiriman & Suhu Placeholder (Ready for next section) -->
    <div v-else-if="activeTab === 2" class="space-y-6">
      <div class="bg-surface-container-lowest rounded-2xl p-8 border border-surface-container shadow-xs text-center flex flex-col items-center justify-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-secondary-fixed flex items-center justify-center text-secondary shadow-xs">
          <span class="material-symbols-outlined text-[36px]">sensors</span>
        </div>
        <div class="max-w-md">
          <h2 class="font-headline-md text-headline-md font-extrabold text-on-surface">
            Lacak Kiriman &amp; Suhu IoT
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Visualisasi peta live GPS armada Reefer dan kurva grafik fluktuasi suhu cold-chain real-time untuk resi <strong class="text-on-surface">{{ shipment.tracking_number }}</strong>.
          </p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-secondary-container text-on-secondary-container font-label-md text-label-md font-bold rounded-full">
          <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
          <span>Sensor Aktif: +{{ shipment.current_temperature }}&deg;C (Transmisi 10s)</span>
        </div>
      </div>
    </div>

    <!-- TAB 3: Laporan & Riwayat Audit Placeholder (Ready for next section) -->
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

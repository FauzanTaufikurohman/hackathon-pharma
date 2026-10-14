<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import HeroSection from '../../components/vendor/HeroSection.vue'
import SearchCard from '../../components/vendor/SearchCard.vue'
import MetricCards from '../../components/vendor/MetricCards.vue'
import PriorityShipmentCard from '../../components/vendor/PriorityShipmentCard.vue'
import CdobGuidelines from '../../components/vendor/CdobGuidelines.vue'
import NotificationToast from '../../components/vendor/NotificationToast.vue'
import type { Shipment } from '../../types/shipment'
import { mockShipments } from '../../data/mockShipments'

const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

// Kargo prioritas pertama: PHA-260104
const priorityShipment = ref<Shipment>(mockShipments[0]!)

function handleSearch(query: string) {
  const found = mockShipments.find(
    s => s.tracking_number.toLowerCase().includes(query.toLowerCase()) ||
         s.item_summary.toLowerCase().includes(query.toLowerCase())
  )

  if (found) {
    priorityShipment.value = found
    toastRef.value?.show(
      `Kargo Ditemukan: ${found.tracking_number}`,
      `Menampilkan data ${found.item_summary}`
    )
  } else {
    toastRef.value?.show(
      `Pencarian: ${query}`,
      `Menghubungkan ke telemetri kargo ${query}...`
    )
  }
}

function handleChipSelect(awb: string, itemName: string) {
  const found = mockShipments.find(s => s.tracking_number === awb)
  if (found) {
    priorityShipment.value = found
  }
  toastRef.value?.show(
    `Resi Terpilih: ${awb}`,
    `Data muatan (${itemName}) telah disinkronkan ke panel pantau.`
  )
}

function handleOpenMonitor(awb: string, screenNumber?: number) {
  const screenNames: Record<number, string> = {
    1: 'Layar 1: Monitor Kesiapan Faskes',
    2: 'Layar 2: Lacak Suhu & GPS Satria Pharma',
    3: 'Layar 3: Laporan Audit & BAP Digital',
  }

  const screenName = screenNumber ? screenNames[screenNumber] : 'Halaman Monitor Pengiriman'
  toastRef.value?.show(
    `Membuka ${awb}`,
    `Mengalihkan ke ${screenName}...`
  )

  // Navigate to monitor page with query param
  setTimeout(() => {
    router.push({
      path: `/vendor/monitor/${awb}`,
      query: screenNumber ? { screen: screenNumber.toString() } : undefined,
    })
  }, 600)
}

function handleConfirmReady(awb: string) {
  priorityShipment.value.recipient_ready = true
  toastRef.value?.show(
    `Kesiapan Faskes Dikonfirmasi!`,
    `Data tersimpan dan notifikasi otomatis diteruskan ke Kurir Satria.`
  )
}
</script>

<template>
  <div class="w-full flex flex-col gap-space-xl pb-space-xl">
    <!-- 1. Hero Greeting & Facility Badge -->
    <HeroSection />

    <!-- 2. Quick Search & Lacak Resi Card -->
    <SearchCard
      :initial-awb="priorityShipment.tracking_number"
      @track="handleSearch"
      @select-chip="handleChipSelect"
    />

    <!-- 3. Operational Metrics Overview (3 Cards) -->
    <MetricCards
      @open-monitor="handleOpenMonitor"
      @open-reports="() => handleOpenMonitor(priorityShipment.tracking_number, 3)"
    />

    <!-- 4. Hero Highlight: Pengiriman Prioritas yang Memerlukan Tindakan Anda -->
    <PriorityShipmentCard
      :shipment="priorityShipment"
      @open-monitor="handleOpenMonitor"
      @confirm-ready="handleConfirmReady"
    />

    <!-- 5. Standar Protokol & Panduan Penerimaan Faskes (CPOB & GDP) -->
    <CdobGuidelines />

    <!-- Notification Toast Simulation -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

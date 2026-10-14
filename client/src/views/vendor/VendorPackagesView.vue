<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import PackagesHeader from '../../components/packages/PackagesHeader.vue'
import PackagesKpiCards from '../../components/packages/PackagesKpiCards.vue'
import PackagesFilterBar from '../../components/packages/PackagesFilterBar.vue'
import ShipmentCardItem from '../../components/packages/ShipmentCardItem.vue'
import RegulatoryBanner from '../../components/packages/RegulatoryBanner.vue'
import NotificationToast from '../../components/vendor/NotificationToast.vue'
import { mockShipments } from '../../data/mockShipments'
import type { Shipment } from '../../types/shipment'

const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

const activeFilter = ref<string>('all')
const searchQuery = ref<string>('')

// Filter counts
const counts = computed(() => {
  return {
    all: mockShipments.length,
    transit: mockShipments.filter(s => s.category === 'transit').length,
    qc: mockShipments.filter(s => s.category === 'qc').length,
    delivered: mockShipments.filter(s => s.category === 'delivered').length,
  }
})

// Filtered shipments
const displayedShipments = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()

  return mockShipments.filter(s => {
    // Category filter
    const matchesCategory =
      activeFilter.value === 'all' || s.category === activeFilter.value

    // Search query
    const targetText = `${s.tracking_number} ${s.item_summary} ${s.sender_name} ${s.recipient_name} ${s.route_display || ''}`.toLowerCase()
    const matchesSearch = query === '' || targetText.includes(query)

    return matchesCategory && matchesSearch
  })
})

function handleRefresh() {
  toastRef.value?.show(
    'Sinkronisasi Sensor Berhasil',
    'Telemetri IoT 4 kargo kargo farmasi telah dimutakhirkan (transmisi 10s).'
  )
}

function handleDownloadRekap() {
  toastRef.value?.show(
    'Mengunduh Rekap CDOB BPOM',
    'Menyiapkan berkas log audit rantai dingin harian (PDF/A)...'
  )
}

function handleShipmentAction(shipment: Shipment) {
  if (shipment.action_type === 'live' || shipment.action_type === 'gps') {
    toastRef.value?.show(
      `Membuka Pelacakan ${shipment.tracking_number}`,
      `Mengalihkan ke telemetri live sensor kargo ${shipment.item_summary}...`
    )
    setTimeout(() => {
      router.push({
        path: `/vendor/monitor/${encodeURIComponent(shipment.tracking_number)}`,
        query: { screen: '2' },
      })
    }, 600)
  } else if (shipment.action_type === 'warehouse') {
    toastRef.value?.show(
      `Pantau Gudang: ${shipment.tracking_number}`,
      `Membuka verifikasi pra-kirim QC Staging Gudang Cakung...`
    )
    setTimeout(() => {
      router.push({
        path: `/vendor/monitor/${encodeURIComponent(shipment.tracking_number)}`,
        query: { screen: '1' },
      })
    }, 600)
  } else if (shipment.action_type === 'pod') {
    toastRef.value?.show(
      `Berita Acara POD: ${shipment.tracking_number}`,
      `Membuka dokumen sertifikasi serah terima e-POD & CPOB...`
    )
    setTimeout(() => {
      router.push({
        path: `/vendor/monitor/${encodeURIComponent(shipment.tracking_number)}`,
        query: { screen: '3' },
      })
    }, 600)
  }
}

function handleOpenGuide() {
  toastRef.value?.show(
    'Pedoman Excursion Suhu BPOM',
    'Membuka panduan mitigasi deviasi suhu CPOB/GDP 2024.3...'
  )
}
</script>

<template>
  <div class="w-full flex flex-col gap-space-lg pb-space-xl">
    <!-- 1. Top Breadcrumb & Header Title -->
    <PackagesHeader
      @refresh="handleRefresh"
      @download-rekap="handleDownloadRekap"
    />

    <!-- 2. Telemetry & Compliance KPI Metric Strip (3 Cards) -->
    <PackagesKpiCards />

    <!-- 3. Search & Filter Bar -->
    <PackagesFilterBar
      v-model:active-filter="activeFilter"
      :counts="counts"
      @search="(q) => searchQuery = q"
    />

    <!-- 4. Consignments Manifest List -->
    <div class="flex flex-col gap-space-md">
      <!-- Empty state if no search results -->
      <div
        v-if="displayedShipments.length === 0"
        class="bg-surface-container-lowest rounded-xl p-space-xl text-center border border-slate-200/80 flex flex-col items-center justify-center gap-2"
      >
        <span class="material-symbols-outlined text-[48px] text-outline">search_off</span>
        <h3 class="font-headline-sm text-on-surface font-bold">Tidak ada kargo yang sesuai</h3>
        <p class="font-body-sm text-on-surface-variant max-w-sm">
          Coba periksa kembali nomor AWB atau kata kunci sediaan obat yang Anda cari.
        </p>
      </div>

      <!-- Shipment Card Items -->
      <ShipmentCardItem
        v-for="shipment in displayedShipments"
        :key="shipment.id"
        :shipment="shipment"
        @action="handleShipmentAction"
      />
    </div>

    <!-- 5. Helpful Regulatory & Process Guidance Banner -->
    <RegulatoryBanner @open-guide="handleOpenGuide" />

    <!-- Toast Notification Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

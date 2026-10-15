<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import CheckerNavbar from '../../components/checker/CheckerNavbar.vue'
import CheckerStagingHeader from '../../components/checker/CheckerStagingHeader.vue'
import CheckerMetricKpis from '../../components/checker/CheckerMetricKpis.vue'
import CheckerQueueFilterBar from '../../components/checker/CheckerQueueFilterBar.vue'
import CheckerQueueCardItem from '../../components/checker/CheckerQueueCardItem.vue'
import CheckerTelemetryDock from '../../components/checker/CheckerTelemetryDock.vue'
import CheckerBarcodeModal from '../../components/checker/CheckerBarcodeModal.vue'
import CheckerFooter from '../../components/checker/CheckerFooter.vue'
import { mockCheckerQueue } from '../../data/mockShipments'
import type { CheckerQueueItem } from '../../types/shipment'

const router = useRouter()

// Active Feature Tab: 1 = Antrean Pemeriksaan, 2 = Form Initial Exam, 3 = Riwayat & Status Approval
const activeTab = ref(1)

// Search & Filter state
const searchQuery = ref('')
const activeFilter = ref<'all' | 'vaccine' | 'recheck'>('all')

// Barcode modal state
const isBarcodeModalOpen = ref(false)

// Manifest modal state
const isManifestModalOpen = ref(false)
const selectedManifestItem = ref<CheckerQueueItem | null>(null)

// Toast state
interface ToastState {
  show: boolean
  message: string
  isSuccess: boolean
}

const toast = ref<ToastState>({
  show: false,
  message: '',
  isSuccess: true,
})

let toastTimer: ReturnType<typeof setTimeout> | null = null

const triggerToast = (message: string, isSuccess = true) => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, isSuccess }
  toastTimer = setTimeout(() => {
    toast.value.show = false
  }, 3200)
}

// Counts for filter pills
const totalCount = computed(() => mockCheckerQueue.length)
const vaccineCount = computed(() => mockCheckerQueue.filter(i => i.category_type === 'vaccine').length)
const recheckCount = computed(() => mockCheckerQueue.filter(i => i.category_type === 'recheck' || i.status === 'recheck').length)

// Filtered Queue list
const filteredQueue = computed(() => {
  return mockCheckerQueue.filter((item) => {
    // Filter pill matching
    if (activeFilter.value === 'vaccine' && item.category_type !== 'vaccine') {
      return false
    }
    if (activeFilter.value === 'recheck' && item.status !== 'recheck' && item.category_type !== 'recheck') {
      return false
    }

    // Search query matching (AWB, batch, commodity title, origin, destination)
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim()
      const matchAwb = item.awb.toLowerCase().includes(q)
      const matchBatch = item.batch_id.toLowerCase().includes(q)
      const matchTitle = item.commodity_title.toLowerCase().includes(q)
      const matchDest = item.destination_label.toLowerCase().includes(q)
      return matchAwb || matchBatch || matchTitle || matchDest
    }

    return true
  })
})

// Tab navigation handler
const handleTabChange = (tab: number) => {
  activeTab.value = tab
  if (tab === 1) {
    triggerToast('Menampilkan Antrean Pemeriksaan Staging Hub Cawang')
  } else if (tab === 2) {
    router.push('/checker/form-initial-exam')
  } else if (tab === 3) {
    router.push('/checker/riwayat')
  }
}

// Copy AWB handler
const handleCopyAwb = (awb: string) => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(awb).then(() => {
      triggerToast(`No. Resi ${awb} disalin ke clipboard`)
    }).catch(() => {
      triggerToast(`No. Resi ${awb} disalin`)
    })
  } else {
    triggerToast(`No. Resi ${awb} disalin`)
  }
}

// Action button click handler
const handleActionClick = (item: CheckerQueueItem) => {
  if (item.status === 'inspection') {
    triggerToast(`Membuka Form Initial Exam untuk resi: ${item.awb}`)
    setTimeout(() => {
      router.push('/checker/form-initial-exam')
    }, 450)
  } else if (item.status === 'recheck') {
    triggerToast(`Memulai Verifikasi Ulang (Re-test) untuk resi: ${item.awb}`)
    setTimeout(() => {
      router.push('/checker/form-initial-exam')
    }, 450)
  } else if (item.status === 'scheduled') {
    selectedManifestItem.value = item
    isManifestModalOpen.value = true
    triggerToast(`Memuat manifest data untuk resi: ${item.awb}`, false)
  }
}

// Barcode scan handler
const handleBarcodeScanned = (scannedAwb: string) => {
  searchQuery.value = scannedAwb
  triggerToast(`Barcode AWB terdeteksi: ${scannedAwb}`)
}

// Telemetry synchronization handler
const handleTelemetrySync = () => {
  triggerToast('Menyinkronkan data sensor staging cold room...')
}
</script>

<template>
  <div class="min-h-screen bg-surface font-body-md text-on-surface antialiased flex flex-col justify-between selection:bg-primary-fixed selection:text-on-primary-fixed">
    <!-- Fixed Header Navigation with 3 Feature Tabs -->
    <CheckerNavbar
      :active-tab="activeTab"
      @tab-change="handleTabChange"
    />

    <!-- Main Container -->
    <main class="w-full pt-24 pb-12 flex-1">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex flex-col w-full">
          <!-- Operational Staging Header Banner -->
          <CheckerStagingHeader />

          <!-- Operational Metric Cards (3 KPI Cards) -->
          <CheckerMetricKpis
            :waiting-inspection-count="3"
            :pending-approval-count="2"
            :passed-count="14"
          />

          <!-- Search & Barcode Scanner Trigger Bar -->
          <CheckerQueueFilterBar
            v-model:search-query="searchQuery"
            :active-filter="activeFilter"
            :total-count="totalCount"
            :vaccine-count="vaccineCount"
            :recheck-count="recheckCount"
            @filter-change="(val) => activeFilter = val as any"
            @open-scanner="isBarcodeModalOpen = true"
          />

          <!-- Queue Cards Section -->
          <div class="flex flex-col gap-5 mb-10">
            <template v-if="filteredQueue.length > 0">
              <CheckerQueueCardItem
                v-for="item in filteredQueue"
                :key="item.awb"
                :item="item"
                @copy-awb="handleCopyAwb"
                @action-click="handleActionClick"
              />
            </template>

            <!-- Empty Search State -->
            <div
              v-else
              class="bg-surface-container-lowest rounded-2xl p-12 text-center shadow-sm border border-surface-container/60 flex flex-col items-center justify-center"
            >
              <div class="w-14 h-14 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant mb-3">
                <span class="material-symbols-outlined text-3xl">inbox</span>
              </div>
              <h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">
                Tidak Ada Antrean yang Cocok
              </h4>
              <p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-1">
                Kriteria pencarian "{{ searchQuery }}" pada filter ini tidak menemukan paket. Silakan periksa kembali No. Resi atau Batch ID.
              </p>
              <button
                type="button"
                @click="searchQuery = ''; activeFilter = 'all'"
                class="mt-4 px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold transition-colors cursor-pointer"
              >
                Reset Filter &amp; Pencarian
              </button>
            </div>
          </div>

          <!-- Hub Cold-Chain Equipment Telemetry Bar (Bottom Dock) -->
          <CheckerTelemetryDock
            hub-location="Hub Gudang Cawang • Staging Cold Room"
            temp-value="3.8°C"
            temp-status="Optimal (2.0°C – 8.0°C)"
            ble-scanner-status="Terhubung (Kamera Aktif)"
            gateway-status="Aktif"
            gateway-latency="18ms"
            @sync="handleTelemetrySync"
          />
        </div>
      </div>
    </main>

    <!-- Footer -->
    <CheckerFooter />

    <!-- Barcode Scanner Modal -->
    <CheckerBarcodeModal
      :is-open="isBarcodeModalOpen"
      @close="isBarcodeModalOpen = false"
      @scanned="handleBarcodeScanned"
    />

    <!-- Manifest Detail Modal for Scheduled Package -->
    <div
      v-if="isManifestModalOpen && selectedManifestItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-surface-container">
        <!-- Modal Header -->
        <div class="p-5 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-secondary text-white flex items-center justify-center shadow-xs">
              <span class="material-symbols-outlined text-xl">description</span>
            </div>
            <div>
              <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
                Manifest Surat Muatan Udara / Darat
              </h3>
              <p class="font-body-sm text-body-sm text-on-surface-variant font-mono">
                AWB: {{ selectedManifestItem.awb }} • Batch: {{ selectedManifestItem.batch_id }}
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="isManifestModalOpen = false"
            class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-4">
          <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container">
            <h4 class="font-label-md text-label-md text-on-surface-variant uppercase font-bold mb-1">
              Komoditas &amp; Spesifikasi Farmasi
            </h4>
            <p class="font-headline-sm text-headline-sm text-on-surface font-bold">
              {{ selectedManifestItem.commodity_title }}
            </p>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
              {{ selectedManifestItem.package_desc }}
            </p>
            <div class="mt-2.5 flex items-center gap-2">
              <span class="bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm px-2.5 py-0.5 rounded-full font-bold">
                {{ selectedManifestItem.temp_range_label }}
              </span>
              <span class="font-body-sm text-body-sm text-on-surface-variant">
                Coolant: High-Density Gel Pack Gel-ICE 4x
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container">
              <span class="font-label-sm text-label-sm text-on-surface-variant block mb-0.5">Pengirim (Shipper):</span>
              <span class="font-body-md text-body-md text-on-surface font-bold block">{{ selectedManifestItem.origin_label }}</span>
            </div>
            <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container">
              <span class="font-label-sm text-label-sm text-on-surface-variant block mb-0.5">Penerima (Consignee):</span>
              <span class="font-body-md text-body-md text-on-surface font-bold block">{{ selectedManifestItem.destination_label }}</span>
            </div>
          </div>

          <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container">
            <span class="font-label-sm text-label-sm text-secondary uppercase font-bold tracking-wider block mb-1">
              Petunjuk Penyimpanan Khusus:
            </span>
            <p class="font-body-sm text-body-sm text-on-surface">
              {{ selectedManifestItem.instruction_desc }}
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-between">
          <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">
            Jadwal Mulai Pemeriksaan: <strong class="text-on-surface font-mono">{{ selectedManifestItem.sla_target_str }}</strong>
          </span>
          <button
            type="button"
            @click="isManifestModalOpen = false"
            class="px-5 py-2.5 rounded-xl bg-primary-container hover:bg-primary text-white font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs"
          >
            Tutup Manifest
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification (exact design from provided HTML) -->
    <div
      class="fixed bottom-6 right-6 bg-inverse-surface text-inverse-on-surface px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 transform transition-all duration-300 z-50 pointer-events-none"
      :class="toast.show ? 'translate-y-0 opacity-100' : 'translate-y-24 opacity-0'"
      id="toastNotification"
    >
      <span class="material-symbols-outlined text-secondary-fixed text-xl" id="toastIcon">
        {{ toast.isSuccess ? 'check_circle' : 'info' }}
      </span>
      <span class="font-label-md text-label-md font-medium" id="toastMessage">
        {{ toast.message }}
      </span>
    </div>
  </div>
</template>

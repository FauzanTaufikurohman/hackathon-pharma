<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import CheckerNavbar from '../../components/checker/CheckerNavbar.vue'
import CheckerHistoryHeader from '../../components/checker/CheckerHistoryHeader.vue'
import CheckerHistoryFilterBar, { type HistoryFilterType } from '../../components/checker/CheckerHistoryFilterBar.vue'
import CheckerHistoryCardItem from '../../components/checker/CheckerHistoryCardItem.vue'
import CheckerHistoryPagination from '../../components/checker/CheckerHistoryPagination.vue'
import CheckerExamDetailModal from '../../components/checker/CheckerExamDetailModal.vue'
import CheckerQuarantineModal from '../../components/checker/CheckerQuarantineModal.vue'
import CheckerAuditTrailModal from '../../components/checker/CheckerAuditTrailModal.vue'
import CheckerFooter from '../../components/checker/CheckerFooter.vue'
import { mockCheckerHistory } from '../../data/mockShipments'
import type { CheckerHistoryItem } from '../../types/shipment'

const router = useRouter()

// Active Feature Tab: 3 = Riwayat & Status Approval
const activeTab = ref(3)

// Filter and search state
const searchQuery = ref('')
const activeFilter = ref<HistoryFilterType>('all')

// Pagination state
const currentPage = ref(1)

// Modals state
const isExamModalOpen = ref(false)
const isQuarantineModalOpen = ref(false)
const isAuditModalOpen = ref(false)
const selectedItem = ref<CheckerHistoryItem | null>(null)

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

// Counts calculation
const allCount = computed(() => 16)
const pendingCount = computed(() => 2)
const approvedCount = computed(() => 13)
const rejectedCount = computed(() => 1)

// Filtered items
const filteredHistory = computed(() => {
  return mockCheckerHistory.filter((item) => {
    // Filter matching
    if (activeFilter.value === 'pending' && item.admin_decision.status !== 'pending') {
      return false
    }
    if (activeFilter.value === 'approved' && item.admin_decision.status !== 'approved') {
      return false
    }
    if (activeFilter.value === 'rejected' && item.admin_decision.status !== 'rejected') {
      return false
    }

    // Search query matching
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim()
      const matchAwb = item.awb.toLowerCase().includes(q)
      const matchTitle = item.commodity_title.toLowerCase().includes(q)
      const matchDate = item.submitted_at.toLowerCase().includes(q)
      const matchNote = item.admin_decision.note.toLowerCase().includes(q)
      return matchAwb || matchTitle || matchDate || matchNote
    }

    return true
  })
})

// Tab navigation handler
const handleTabChange = (tab: number) => {
  activeTab.value = tab
  if (tab === 1) {
    router.push('/checker')
  } else if (tab === 2) {
    router.push('/checker/form-initial-exam')
  } else if (tab === 3) {
    triggerToast('Menampilkan Riwayat Pemeriksaan & Status Persetujuan Admin')
  }
}

// Action button click handler
const handleActionClick = (item: CheckerHistoryItem) => {
  selectedItem.value = item
  if (item.admin_decision.action_button_type === 'exam_detail') {
    isExamModalOpen.value = true
    triggerToast(`Membuka Detail Formulir Inspeksi ${item.awb}`)
  } else if (item.admin_decision.action_button_type === 'pdf_download') {
    triggerToast(`Mengunduh Lembar Verifikasi QC (${item.awb}.pdf)...`)
  } else if (item.admin_decision.action_button_type === 'quarantine_doc') {
    isQuarantineModalOpen.value = true
    triggerToast(`Membuka Berita Acara Karantina ${item.awb}`, false)
  } else if (item.admin_decision.action_button_type === 'audit_trail') {
    isAuditModalOpen.value = true
    triggerToast(`Membuka Log Audit Trail Kriptografis CDOB`)
  }
}

const handlePageChange = (page: number) => {
  if (page < 1 || page > 3) return
  currentPage.value = page
  triggerToast(`Beralih ke Halaman ${page}`)
}

const handleDownloadBap = () => {
  triggerToast('Mencetak Berita Acara Karantina BAP-KRN-2026-095...')
}
</script>

<template>
  <div class="min-h-screen bg-surface font-body-md text-on-surface antialiased flex flex-col justify-between selection:bg-primary-fixed selection:text-on-primary-fixed">
    <!-- Header Navigation with 3 Feature Tabs -->
    <CheckerNavbar
      :active-tab="activeTab"
      @tab-change="handleTabChange"
    />

    <!-- Main Container -->
    <main class="w-full pt-24 pb-12 flex-1">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex flex-col w-full">
          <!-- Page Context & Status Banner -->
          <CheckerHistoryHeader
            :total-tickets="allCount"
            :pending-review="pendingCount"
            approval-rate="93.8%"
          />

          <!-- Operational Filter Row & Search -->
          <CheckerHistoryFilterBar
            v-model:search-query="searchQuery"
            :active-filter="activeFilter"
            :all-count="allCount"
            :pending-count="pendingCount"
            :approved-count="approvedCount"
            :rejected-count="rejectedCount"
            @filter-change="(val) => activeFilter = val"
          />

          <!-- Cards Container: Aligned with inspections & approvals entities -->
          <div class="space-y-6">
            <template v-if="filteredHistory.length > 0">
              <CheckerHistoryCardItem
                v-for="item in filteredHistory"
                :key="item.awb"
                :item="item"
                @action-click="handleActionClick"
              />
            </template>

            <!-- Empty Search State -->
            <div
              v-else
              class="bg-surface-container-lowest rounded-xl p-12 text-center shadow-sm border border-surface-container/60 flex flex-col items-center justify-center"
            >
              <div class="w-14 h-14 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant mb-3">
                <span class="material-symbols-outlined text-3xl">inbox</span>
              </div>
              <h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">
                Tidak Ada Riwayat Pemeriksaan Ditemukan
              </h4>
              <p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-1">
                Kriteria pencarian "{{ searchQuery }}" pada filter ini tidak menemukan catatan riwayat. Silakan periksa kembali No. Resi atau nama komoditas.
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

          <!-- Pagination & Certification Audit Trail Footer -->
          <CheckerHistoryPagination
            :current-page="currentPage"
            :displayed-count="filteredHistory.length"
            :total-count="allCount"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </main>

    <!-- Footer -->
    <CheckerFooter />

    <!-- Modal 1: Detail Formulir Inspeksi -->
    <CheckerExamDetailModal
      :is-open="isExamModalOpen"
      :item="selectedItem"
      @close="isExamModalOpen = false"
    />

    <!-- Modal 2: Berita Acara Karantina -->
    <CheckerQuarantineModal
      :is-open="isQuarantineModalOpen"
      :item="selectedItem"
      @close="isQuarantineModalOpen = false"
      @download-bap="handleDownloadBap"
    />

    <!-- Modal 3: Audit Trail Log -->
    <CheckerAuditTrailModal
      :is-open="isAuditModalOpen"
      :item="selectedItem"
      @close="isAuditModalOpen = false"
    />

    <!-- Toast Notification -->
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

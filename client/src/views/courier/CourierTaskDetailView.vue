<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mockCourierTasks } from '../../data/mockShipments'
import type { CourierTaskItem } from '../../types/shipment'
import CourierTaskDetailNavbar from '../../components/courier/CourierTaskDetailNavbar.vue'
import CourierIncomingUrgencyBanner from '../../components/courier/CourierIncomingUrgencyBanner.vue'
import CourierIncomingCardHeader from '../../components/courier/CourierIncomingCardHeader.vue'
import CourierIncomingTelemetryGrid from '../../components/courier/CourierIncomingTelemetryGrid.vue'
import CourierIncomingRouteCol from '../../components/courier/CourierIncomingRouteCol.vue'
import CourierIncomingSpecsCol from '../../components/courier/CourierIncomingSpecsCol.vue'
import CourierIncomingPreTripChecklist from '../../components/courier/CourierIncomingPreTripChecklist.vue'
import CourierIncomingActionFooter from '../../components/courier/CourierIncomingActionFooter.vue'
import CourierIncomingRejectModal from '../../components/courier/CourierIncomingRejectModal.vue'
import CourierIncomingSuccessBanner from '../../components/courier/CourierIncomingSuccessBanner.vue'
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

// Checklist state
const check1 = ref(false)
const check2 = ref(false)
const check3 = ref(false)

// Accept / Processing states
const isProcessing = ref(false)
const isAccepted = ref(task.value.status === 'in_transit')
const isRejectModalOpen = ref(false)

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
    `Beralih ke tampilan ${tabName} untuk resi ${task.value.awb}...`
  )

  if (tabNumber === 2) {
    // If user clicks Tab 2 directly
    router.push(`/vendor/monitor/${task.value.awb}?screen=2`)
  } else if (tabNumber === 4) {
    router.push(`/vendor/monitor/${task.value.awb}?screen=3`)
  }
}

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
      `Resi ${task.value.awb} telah tersinkron ke Pengantaran & Monitor.`
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
  router.push(`/vendor/monitor/${task.value.awb}?screen=2`)
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
        <div class="flex flex-col w-full space-y-6">
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
      </div>
    </main>

    <!-- Rejection Modal Dialog -->
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

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import CourierTasksHeader from '../../components/courier/CourierTasksHeader.vue'
import CourierTasksFilterBar from '../../components/courier/CourierTasksFilterBar.vue'
import CourierTaskCardItem from '../../components/courier/CourierTaskCardItem.vue'
import CourierTaskActionModal from '../../components/courier/CourierTaskActionModal.vue'
import CourierComplianceFooterBanner from '../../components/courier/CourierComplianceFooterBanner.vue'
import NotificationToast from '../../components/vendor/NotificationToast.vue'
import type { CourierTaskItem, CourierTaskStatus } from '../../types/shipment'
import { mockCourierTasks } from '../../data/mockShipments'

const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

// Task list state
const tasks = ref<CourierTaskItem[]>([...mockCourierTasks])

// Filter & search state
const searchQuery = ref('')
const activeFilter = ref<string>('assigned') // Default to 'assigned' (Perlu Konfirmasi) as shown in template

// Modal state
const isModalOpen = ref(false)
const selectedTask = ref<CourierTaskItem | null>(null)

// Counts calculation
const counts = computed(() => {
  return {
    all: tasks.value.length,
    assigned: tasks.value.filter(t => t.status === 'assigned').length,
    in_transit: tasks.value.filter(t => t.status === 'in_transit').length,
    hold: tasks.value.filter(t => t.status === 'hold').length,
    delivered: tasks.value.filter(t => t.status === 'delivered').length,
  }
})

// Filtered tasks
const filteredTasks = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  return tasks.value.filter(task => {
    // Status filter
    const matchesStatus =
      activeFilter.value === 'all' || task.status === activeFilter.value

    // Search query filter
    const matchesSearch =
      !query ||
      task.awb.toLowerCase().includes(query) ||
      task.item_title.toLowerCase().includes(query) ||
      task.destination_name.toLowerCase().includes(query) ||
      task.destination_address.toLowerCase().includes(query) ||
      task.origin_name.toLowerCase().includes(query)

    return matchesStatus && matchesSearch
  })
})

function handleActionClick(task: CourierTaskItem) {
  if (task.status === 'assigned') {
    selectedTask.value = task
    isModalOpen.value = true
  } else if (task.status === 'in_transit') {
    toastRef.value?.show(
      'Navigasi Monitor Satria',
      `Membuka telemetry rute live dan sensor probe 4.5°C untuk ${task.awb}...`
    )
    router.push(`/vendor/monitor/${task.awb}?screen=2`)
  } else if (task.status === 'delivered') {
    toastRef.value?.show(
      'Berita Acara Penerimaan (e-POD)',
      `Membuka sertifikat BAP dan tanda tangan SIP Apoteker untuk ${task.awb}...`
    )
    router.push(`/vendor/monitor/${task.awb}?screen=3`)
  }
}

function handleAcceptTask(task: CourierTaskItem) {
  const target = tasks.value.find(t => t.id === task.id)
  if (target) {
    target.status = 'in_transit'
    target.status_label = 'Dalam Perjalanan (in_transit)'
    target.cta_label = 'Lanjutkan & Monitor Suhu'
    target.cta_type = 'monitor'
    target.hint_text = 'Menuju Tab 2: Pengantaran & Monitor'
  }
  isModalOpen.value = false
  toastRef.value?.show(
    'Penugasan Kargo Diterima!',
    `Penugasan AWB: ${task.awb} berhasil diterima. Membuka Ruang Eksekusi Tugas Rantai Dingin.`
  )
}

function handleRejectTask(payload: { task: CourierTaskItem; reason: string }) {
  const index = tasks.value.findIndex(t => t.id === payload.task.id)
  if (index !== -1) {
    tasks.value.splice(index, 1)
  }
  isModalOpen.value = false
  toastRef.value?.show(
    'Penugasan Ditolak',
    `Tugas ${payload.task.awb} ditolak (${payload.reason}) dan dikembalikan ke Dispatcher Hub Cawang.`
  )
}
</script>

<template>
  <div class="flex flex-col w-full">
    <!-- Header & Live Operational Snapshot -->
    <CourierTasksHeader
      :cooler-box-temp="4.2"
      :completed-count="counts.delivered"
      :total-count="counts.all"
    />

    <!-- Search & Filter Controls -->
    <div class="mt-6">
      <CourierTasksFilterBar
        v-model:search-query="searchQuery"
        v-model:active-filter="activeFilter"
        :counts="counts"
      />
    </div>

    <!-- Task Summary Cards List -->
    <div class="flex flex-col gap-4" id="task-card-list">
      <template v-if="filteredTasks.length > 0">
        <CourierTaskCardItem
          v-for="task in filteredTasks"
          :key="task.id"
          :task="task"
          @action-click="handleActionClick"
        />
      </template>

      <!-- Empty State -->
      <div
        v-else
        class="bg-surface-container-lowest rounded-2xl p-12 text-center flex flex-col items-center justify-center gap-3 border border-surface-container/60 shadow-xs"
      >
        <div class="w-14 h-14 rounded-2xl bg-surface-container-low flex items-center justify-center text-outline">
          <span class="material-symbols-outlined text-[32px]">inventory</span>
        </div>
        <h3 class="font-headline-sm font-bold text-on-surface">Tidak Ada Tugas Ditemukan</h3>
        <p class="font-body-sm text-on-surface-variant max-w-sm">
          Tidak ada tugas pengiriman farmasi yang cocok dengan filter atau kata kunci pencarian Anda.
        </p>
        <button
          type="button"
          @click="activeFilter = 'all'; searchQuery = ''"
          class="mt-2 px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md font-semibold transition-colors cursor-pointer"
        >
          Reset Filter &amp; Pencarian
        </button>
      </div>
    </div>

    <!-- Bottom Regulatory & Dispatch Compliance Strip -->
    <CourierComplianceFooterBanner />

    <!-- Interactive Execution Modal Sheet -->
    <CourierTaskActionModal
      :task="selectedTask"
      :is-open="isModalOpen"
      @close="isModalOpen = false"
      @accept="handleAcceptTask"
      @reject="handleRejectTask"
    />

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

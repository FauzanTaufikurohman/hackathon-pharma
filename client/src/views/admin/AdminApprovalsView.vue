<script setup lang="ts">
import { ref, computed } from 'vue'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'
import AdminApprovalsHeader from '../../components/admin/AdminApprovalsHeader.vue'
import AdminShipmentSelectorBar from '../../components/admin/AdminShipmentSelectorBar.vue'
import AdminQcGateReviewCard from '../../components/admin/AdminQcGateReviewCard.vue'
import AdminCourierGateDispatchCard from '../../components/admin/AdminCourierGateDispatchCard.vue'
import AdminFooter from '../../components/admin/AdminFooter.vue'
import { mockAdminApprovalShipments, mockAdminCouriers } from '../../data/mockShipments'
import type { AdminApprovalShipment } from '../../types/shipment'

// Active Selected Shipment
const selectedAwb = ref<string>('PHA-260104')
const shipments = ref([...mockAdminApprovalShipments])
const couriers = ref([...mockAdminCouriers])

const currentShipment = computed<AdminApprovalShipment>(() => {
  return shipments.value.find((s) => s.awb === selectedAwb.value) || shipments.value[0]!
})

// Decision Gate State ('pending' | 'approved' | 'held')
const decisionStatus = ref<'pending' | 'approved' | 'held'>('pending')
const selectedCourierId = ref<string>('courier-1')
const isDispatching = ref(false)
const isDispatched = ref(false)

// Toast Notification
const toastMessage = ref<{ title: string; desc: string; isError?: boolean } | null>(null)
let toastTimeout: ReturnType<typeof setTimeout> | null = null

function showToast(title: string, desc: string, isError = false) {
  if (toastTimeout) clearTimeout(toastTimeout)
  toastMessage.value = { title, desc, isError }
  toastTimeout = setTimeout(() => {
    toastMessage.value = null
  }, 4000)
}

// Photo Zoom Modal
const isPhotoModalOpen = ref(false)
const modalPhotoUrl = ref('')

function handleViewPhoto(url: string) {
  modalPhotoUrl.value = url
  isPhotoModalOpen.value = true
}

// Shipment selection change
function handleSelectShipment(awb: string) {
  selectedAwb.value = awb
  decisionStatus.value = 'pending'
  isDispatched.value = false
  isDispatching.value = false
  showToast('Manifest Dipilih', `Memuat data inspeksi kargo ${awb}.`)
}

// Gate 1 Decision Handlers
function handleApprove(reason: string) {
  decisionStatus.value = 'approved'
  showToast(
    'QC Lolos & Disetujui!',
    'Status decision = approved. Otorisasi dispatch kurir dibuka penuh.'
  )
}

function handleHold(reason: string) {
  decisionStatus.value = 'held'
  showToast(
    'Pengiriman Ditahan (Held)',
    'Status kargo diubah ke decision = held. Alokasi kurir terkunci.',
    true
  )
}

// Gate 2 Courier Handlers
function handleSelectCourier(courierId: string) {
  selectedCourierId.value = courierId
}

function handleDispatch(params: { courierId: string; notifyBot: boolean; enableBeacon: boolean }) {
  isDispatching.value = true
  setTimeout(() => {
    isDispatching.value = false
    isDispatched.value = true
    const courierObj = couriers.value.find((c) => c.id === params.courierId)
    const courierName = courierObj ? `${courierObj.name} (${courierObj.code})` : 'Kurir-007'
    showToast(
      'Penugasan Kurir Berhasil!',
      `Notifikasi dispatch otomatis telah dikirim ke WhatsApp & Telegram ${courierName}.`
    )
  }, 1000)
}
</script>

<template>
  <div class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <!-- Navbar Header -->
    <AdminNavbar active-tab="approvals" />

    <!-- Main Content Container -->
    <main class="w-full max-w-7xl mx-auto px-6 py-8 flex-1 bg-surface">
      <div class="flex flex-col w-full">
        <!-- Top Section Header -->
        <AdminApprovalsHeader :queue-count="shipments.length" />

        <!-- Context Shipment Selector Bar -->
        <AdminShipmentSelectorBar
          :shipments="shipments"
          :selected-awb="selectedAwb"
          @select-shipment="handleSelectShipment"
        />

        <!-- Split Workspace (2 Equal Columns: Gate 1 and Gate 2) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
          <!-- LEFT CARD: GERBANG 1 - Review Pemeriksaan Checker & Official Admin Decision -->
          <AdminQcGateReviewCard
            :shipment="currentShipment"
            :decision-status="decisionStatus"
            @approve="handleApprove"
            @hold="handleHold"
            @view-photo="handleViewPhoto"
          />

          <!-- RIGHT CARD: GERBANG 2 - Alokasi Armada & Penugasan Kurir Satria -->
          <AdminCourierGateDispatchCard
            :couriers="couriers"
            :decision-status="decisionStatus"
            :selected-courier-id="selectedCourierId"
            :is-dispatching="isDispatching"
            :is-dispatched="isDispatched"
            @select-courier="handleSelectCourier"
            @dispatch="handleDispatch"
          />
        </div>
      </div>
    </main>

    <!-- Footer -->
    <AdminFooter />

    <!-- Notification Toast Container (Dynamic feedback) -->
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
        class="fixed bottom-6 right-6 z-50 pointer-events-auto"
      >
        <div
          :class="[
            'px-5 py-3.5 rounded-xl shadow-xl flex items-center gap-3 border',
            toastMessage.isError
              ? 'bg-error-container text-error border-error/30'
              : 'bg-inverse-surface text-inverse-on-surface border-surface-container-high',
          ]"
        >
          <span
            :class="[
              'material-symbols-outlined text-xl',
              toastMessage.isError ? 'text-error' : 'text-secondary-fixed',
            ]"
          >
            {{ toastMessage.isError ? 'warning' : 'check_circle' }}
          </span>
          <div class="flex flex-col">
            <span class="font-label-md text-label-md font-bold">{{ toastMessage.title }}</span>
            <span class="font-body-sm text-body-sm opacity-90">{{ toastMessage.desc }}</span>
          </div>
          <button
            type="button"
            @click="toastMessage = null"
            class="ml-2 text-current opacity-70 hover:opacity-100 cursor-pointer"
          >
            <span class="material-symbols-outlined text-sm">close</span>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Photo Zoom Modal -->
    <div
      v-if="isPhotoModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
      @click.self="isPhotoModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-2xl w-full p-4 shadow-2xl overflow-hidden border border-surface-container">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container-low mb-3">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">camera_alt</span>
            <span class="font-headline-sm text-headline-sm text-on-surface font-bold">Bukti Foto Initial Exam ({{ selectedAwb }})</span>
          </div>
          <button
            type="button"
            @click="isPhotoModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="rounded-xl overflow-hidden bg-black max-h-[70vh] flex items-center justify-center">
          <img
            :src="modalPhotoUrl"
            alt="Bukti Foto Pemeriksaan Fisik & Sensor Suhu"
            class="max-h-full max-w-full object-contain"
          />
        </div>

        <div class="mt-3 bg-surface-container-low p-3 rounded-xl flex items-center justify-between text-on-surface-variant font-body-sm text-body-sm">
          <span>Thermometer LCD: <strong>4.2°C</strong> • Tamper Seal: <strong>SL-8891</strong></span>
          <span class="text-secondary font-bold flex items-center gap-1">
            <span class="material-symbols-outlined text-base">verified</span> AI Vision Verified
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

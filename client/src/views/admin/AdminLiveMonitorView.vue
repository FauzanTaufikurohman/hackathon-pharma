<script setup lang="ts">
import { ref } from 'vue'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'
import AdminLiveMonitorHeader from '../../components/admin/AdminLiveMonitorHeader.vue'
import AdminAiPredictiveAnomalyBanner from '../../components/admin/AdminAiPredictiveAnomalyBanner.vue'
import AdminFleetMapCard from '../../components/admin/AdminFleetMapCard.vue'
import AdminTelemetryCurveCard from '../../components/admin/AdminTelemetryCurveCard.vue'
import AdminIncidentDecisionPanel from '../../components/admin/AdminIncidentDecisionPanel.vue'
import AdminFooter from '../../components/admin/AdminFooter.vue'
import { mockAdminIncidentData } from '../../data/mockShipments'

// Incident Data state
const incident = ref({ ...mockAdminIncidentData })
const isSubmitting = ref(false)

// Toast Notification
const toastMessage = ref<{ title: string; desc: string; isError?: boolean } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null

function showToast(title: string, desc: string, isError = false) {
  if (toastTimer) clearTimeout(toastTimer)
  toastMessage.value = { title, desc, isError }
  toastTimer = setTimeout(() => {
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

// Sensor Detail Modal
const isSensorModalOpen = ref(false)

function handleViewSensorDetail() {
  isSensorModalOpen.value = true
}

// Send WA Alert handler
function handleSendWaAlert() {
  showToast(
    'Peringatan Terkirim!',
    'Instruksi peringatan tren suhu (+0.4°C/10 mnt) telah dikirim ke WhatsApp Kurir Bambang.'
  )
}

// Decision Submit handler
function handleSubmitDecision(payload: { action: 'return' | 'recheck' | 'resume'; reason: string }) {
  isSubmitting.value = true
  setTimeout(() => {
    isSubmitting.value = false
    const actionLabel =
      payload.action === 'return'
        ? 'RETUR GUDANG ASAL (returning)'
        : payload.action === 'recheck'
        ? 'PERIKSA ULANG HUB (inspection)'
        : 'LANJUTKAN PENGANTARAN (in_transit)'

    showToast(
      'Keputusan Resmi Terkirim!',
      `Status kargo PHA-260104 diperbarui ke ${actionLabel}. Notifikasi WA otomatis terkirim ke kurir dan RS Medika Sehat.`
    )
  }, 1000)
}
</script>

<template>
  <div class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">
    <!-- Navbar Header -->
    <AdminNavbar active-tab="monitor" />

    <!-- Main Content Container -->
    <main class="w-full max-w-7xl mx-auto px-6 py-8 flex-1 bg-surface">
      <div class="flex flex-col w-full space-y-6">
        <!-- TOP HEADER SECTION -->
        <AdminLiveMonitorHeader />

        <!-- FR-04: AI PREDICTIVE ANOMALY BANNER -->
        <AdminAiPredictiveAnomalyBanner
          @view-sensor-detail="handleViewSensorDetail"
          @send-wa-alert="handleSendWaAlert"
        />

        <!-- MAIN SPLIT WORKSPACE: 7 COLS (TELEMETRY & FLEET) vs 5 COLS (INCIDENT DECISIONING) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          <!-- LEFT COLUMN: LIVE FLEET GPS & TEMPERATURE RIBBON (7 COLS) -->
          <div class="lg:col-span-7 flex flex-col gap-6">
            <!-- FLEET MAP CARD -->
            <AdminFleetMapCard />

            <!-- REAL-TIME TELEMETRY CURVE CARD -->
            <AdminTelemetryCurveCard />
          </div>

          <!-- RIGHT COLUMN: PANEL KEPUTUSAN KENDALA LAPANGAN (5 COLS) -->
          <div class="lg:col-span-5 flex flex-col gap-6">
            <AdminIncidentDecisionPanel
              :incident="incident"
              :is-submitting="isSubmitting"
              @submit-decision="handleSubmitDecision"
              @view-photo="handleViewPhoto"
            />
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <AdminFooter />

    <!-- Notification Toast -->
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
            <span class="material-symbols-outlined text-error text-xl">broken_image</span>
            <span class="font-headline-sm text-headline-sm text-on-surface font-bold">Bukti Foto Segel Robek (#ISSUE-8841)</span>
          </div>
          <button
            type="button"
            @click="isPhotoModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg cursor-pointer"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="rounded-xl overflow-hidden bg-black max-h-[70vh] flex items-center justify-center">
          <img
            :src="modalPhotoUrl"
            alt="Foto Bukti Lapangan Segel Rusak"
            class="max-h-full max-w-full object-contain"
          />
        </div>

        <div class="mt-3 bg-surface-container-low p-3 rounded-xl flex items-center justify-between text-on-surface-variant font-body-sm text-body-sm">
          <span>{{ incident.gps_coords }}</span>
          <span class="font-mono text-primary font-semibold">{{ incident.hash }}</span>
        </div>
      </div>
    </div>

    <!-- Sensor Detail Modal -->
    <div
      v-if="isSensorModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="isSensorModalOpen = false"
    >
      <div class="bg-surface-container-lowest rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-surface-container">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container-low mb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-tertiary text-2xl">query_stats</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Detail Sensor AI Anomaly (PHA-260102)</h3>
          </div>
          <button
            type="button"
            @click="isSensorModalOpen = false"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg cursor-pointer"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="space-y-4">
          <div class="bg-tertiary-fixed/30 p-4 rounded-xl border border-tertiary-fixed flex flex-col gap-1.5">
            <span class="font-label-sm text-label-sm text-tertiary-container uppercase tracking-wider font-bold">
              Prediksi Anomali Termal Micro-Slope
            </span>
            <div class="flex items-baseline gap-2">
              <span class="font-telemetry-value text-telemetry-value text-on-surface font-bold">7.2°C</span>
              <span class="font-label-md text-label-md text-error font-semibold">+0.4°C / 10 mnt</span>
            </div>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Suhu diproyeksikan menembus batas kritis 8.0°C dalam 22 menit jika cooler box tidak ditutup rapat.
            </p>
          </div>

          <div class="bg-surface-container-low p-3.5 rounded-xl space-y-2 text-on-surface font-body-sm text-body-sm">
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Model Sensor:</span>
              <span class="font-semibold">Sensirion SHT40 (Dual-Probe)</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Lokasi Kompartemen:</span>
              <span>Reefer Rear Section #B</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Sampling Rate:</span>
              <span>10 Detik • MQTT QoS 1</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Status Baterai:</span>
              <span class="text-secondary font-bold">94% (Sehat)</span>
            </div>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button
              type="button"
              @click="isSensorModalOpen = false"
              class="px-4 py-2 rounded-lg bg-surface-container-low text-on-surface font-label-md hover:bg-surface-container cursor-pointer"
            >
              Tutup
            </button>
            <button
              type="button"
              @click="isSensorModalOpen = false; handleSendWaAlert()"
              class="px-4 py-2 rounded-lg bg-tertiary text-on-tertiary font-label-md hover:bg-tertiary-container cursor-pointer font-semibold flex items-center gap-1.5"
            >
              <span class="material-symbols-outlined text-base">chat</span>
              Kirim WA ke Kurir
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

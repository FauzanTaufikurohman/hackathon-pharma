<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import CourierDutyBanner from '../../components/courier/CourierDutyBanner.vue'
import CourierMetricCards from '../../components/courier/CourierMetricCards.vue'
import CourierPriorityTaskCard from '../../components/courier/CourierPriorityTaskCard.vue'
import CourierRouteVisualizerCard from '../../components/courier/CourierRouteVisualizerCard.vue'
import CourierProtocolCard from '../../components/courier/CourierProtocolCard.vue'
import CourierHotlineCard from '../../components/courier/CourierHotlineCard.vue'
import NotificationToast from '../../components/vendor/NotificationToast.vue'
import { mockCourierProfile, mockCourierMetrics, mockShipments } from '../../data/mockShipments'

const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

// Primary shipment for courier assignment hook
const priorityShipment = mockShipments[0]!

function handleCopyAwb(awb: string) {
  toastRef.value?.show(
    'No. AWB Disalin!',
    `Nomor resi ${awb} berhasil disalin ke clipboard.`
  )
}

function handleCardClick(type: string) {
  const typeMap: Record<string, string> = {
    new: 'Tugas Baru Masuk (Butuh konfirmasi penugasan)',
    in_transit: 'Sedang Diantar (Rute aktif Cawang - Salemba)',
    hold: 'Ditahan / Kendala (Nol kendala saat ini)',
    completed: 'Selesai Hari Ini (3 kargo sukses e-POD)',
  }
  toastRef.value?.show(
    'Ringkasan Status Tugas',
    typeMap[type] || 'Menampilkan ringkasan tugas kurir Satria.'
  )
}

function handleProcessTask(awb: string) {
  toastRef.value?.show(
    'Membuka Tugas Kargo',
    `Mempersiapkan rute dan formulir serah terima untuk ${awb}...`
  )
}
</script>

<template>
  <div class="flex flex-col w-full">
    <!-- Top Readiness & Duty Banner -->
    <CourierDutyBanner :profile="mockCourierProfile" />

    <!-- 4 Summary Metric Cards -->
    <CourierMetricCards
      :metrics="mockCourierMetrics"
      @card-click="handleCardClick"
    />

    <!-- Bottom Split Section (2/3 Left, 1/3 Right) -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8 items-start">
      <!-- Left Column (2/3 width) - "Tugas Prioritas Saat Ini" -->
      <div class="lg:col-span-2 flex flex-col gap-6">
        <!-- Spotlight Priority Task Card -->
        <CourierPriorityTaskCard
          :awb="priorityShipment.tracking_number"
          :item-title="`${priorityShipment.item_summary} (2 Koli / 400 Dosis)`"
          :batch-number="priorityShipment.batch_number"
          biofarma-id="#BF-901"
          origin-hub="Hub Gudang Cawang, Jakarta Timur"
          origin-bay="Dermaga Dingin Staging Bay 3B"
          :destination-name="priorityShipment.recipient_name"
          destination-address="Instalasi Farmasi Gd. B Lt. 1, Jl. Raya Salemba No. 12"
          deadline-time="Maks. 15:30 WIB"
          :travel-duration-min="24"
          seal-number="SL-8891"
          seal-status="Status: Terkunci Rapat"
          probe-sensor-id="IoT Probe ST-300"
          :probe-temp="4.2"
          probe-status-text="(Optimal)"
          pharmacist-in-charge="apt. Nadia Rahma, S.Farm (SIP: 198902-RSMS)"
          status-label="Menunggu Jawaban Satria"
          @copy-awb="handleCopyAwb"
          @process-task="handleProcessTask"
        />

        <!-- Quick Delivery Map / Route Visualizer -->
        <CourierRouteVisualizerCard
          corridor-name="Koridor Pengiriman Jalur Cepat (Bypass Salemba)"
          traffic-status="Lalu Lintas: Lancar Hijau"
          :ambient-temp="31"
          :distance-km="9.8"
          :duration-min="24"
        />
      </div>

      <!-- Right Column (1/3 width) - "Panduan Singkat CPOB Satria" -->
      <div class="flex flex-col gap-6">
        <!-- CPOB Protocol Card -->
        <CourierProtocolCard />

        <!-- Emergency Escalation & Hotline Box -->
        <CourierHotlineCard />
      </div>
    </section>

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

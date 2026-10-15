<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mockCourierTasks } from '../../data/mockShipments'
import type { CourierTaskItem } from '../../types/shipment'
import CourierTaskDetailNavbar from '../../components/courier/CourierTaskDetailNavbar.vue'

// Tab 2: Pengantaran & Monitor Components
import CourierDeliveryStepper from '../../components/courier/CourierDeliveryStepper.vue'
import CourierDeliveryMapCard from '../../components/courier/CourierDeliveryMapCard.vue'
import CourierDeliveryRecipientCard from '../../components/courier/CourierDeliveryRecipientCard.vue'
import CourierDeliveryTelemetryPanel from '../../components/courier/CourierDeliveryTelemetryPanel.vue'
import CourierDeliveryIssueBanner from '../../components/courier/CourierDeliveryIssueBanner.vue'

// Tab 3: Lapor Kendala Components
import CourierIssueNoticeBanner from '../../components/courier/CourierIssueNoticeBanner.vue'
import CourierIssueReportForm from '../../components/courier/CourierIssueReportForm.vue'
import CourierIssueDecisionCard from '../../components/courier/CourierIssueDecisionCard.vue'
import CourierIssueAuditLogStrip from '../../components/courier/CourierIssueAuditLogStrip.vue'

// Tab 4: Serah Terima & Laporan Components
import CourierHandoverBanner from '../../components/courier/CourierHandoverBanner.vue'
import CourierHandoverEpodCard from '../../components/courier/CourierHandoverEpodCard.vue'
import CourierHandoverCargoSummaryCard from '../../components/courier/CourierHandoverCargoSummaryCard.vue'
import CourierHandoverTimelineCard from '../../components/courier/CourierHandoverTimelineCard.vue'
import CourierHandoverReportCard from '../../components/courier/CourierHandoverReportCard.vue'

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

// Synchronize tab with route query
watch(
  () => route.query.tab,
  newTab => {
    if (newTab) {
      activeTab.value = Number(newTab)
    }
  }
)

// Tab 1: Checklist & Action state
const check1 = ref(false)
const check2 = ref(false)
const check3 = ref(false)
const isProcessing = ref(false)
const isAccepted = ref(false) // Default: not accepted yet until courier confirms
const isRejectModalOpen = ref(false)
const rejectCategory = ref('equipment_failure')
const rejectNotes = ref('')

const checklistCount = computed(() => {
  let count = 0
  if (check1.value) count++
  if (check2.value) count++
  if (check3.value) count++
  return count
})

// Countdown Timer (14:48 -> 00:00)
const remainingSeconds = ref(14 * 60 + 48)
let timerInterval: ReturnType<typeof setInterval> | null = null

const countdownDisplay = computed(() => {
  const m = Math.floor(remainingSeconds.value / 60)
    .toString()
    .padStart(2, '0')
  const s = (remainingSeconds.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

onMounted(() => {
  timerInterval = setInterval(() => {
    if (remainingSeconds.value > 0) {
      remainingSeconds.value--
    } else if (timerInterval) {
      clearInterval(timerInterval)
    }
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

// Tab 2: Delivery & Arrival state
const hasArrived = ref(false)
const arrivedTime = ref('14:50')

function handleLockedTabClick(tabNumber: number) {
  const tabLabels: Record<number, string> = {
    2: 'Pengantaran & Monitor',
    3: 'Lapor Kendala',
    4: 'Serah Terima & Laporan',
  }
  const name = tabLabels[tabNumber] || 'Halaman Ini'
  toastRef.value?.show(
    `Navigasi Terkunci: ${name}`,
    'Tugas Masuk belum diterima. Harap selesaikan checklist dan klik "Terima Tugas Pengantaran" terlebih dahulu.'
  )
}

function handleTabChange(tabNumber: number) {
  if (tabNumber !== 1 && !isAccepted.value) {
    handleLockedTabClick(tabNumber)
    return
  }

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
    `Beralih ke tampilan ${tabName} untuk kargo ${task.value.awb}...`
  )
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Tab 1 Actions
function handleAcceptTask() {
  if (isAccepted.value) return

  if (checklistCount.value < 3) {
    const proceed = window.confirm(
      'Perhatian: Anda belum menyelesaikan seluruh 3 poin Checklist Pra-Jalan Satria. Tetap verifikasi dan terima tugas pengantaran ini?'
    )
    if (!proceed) return
    check1.value = true
    check2.value = true
    check3.value = true
  }

  isProcessing.value = true

  setTimeout(() => {
    isProcessing.value = false
    isAccepted.value = true
    task.value.status = 'in_transit'
    task.value.status_label = 'Dalam Perjalanan (in_transit)'

    toastRef.value?.show(
      'Tugas Berhasil Diterima!',
      `Resi ${task.value.awb} telah tersinkron ke Pengantaran & Monitor. Navigasi pengantaran kini terbuka!`
    )
  }, 500)
}

function openRejectModal() {
  isRejectModalOpen.value = true
}

function closeRejectModal() {
  isRejectModalOpen.value = false
}

function submitRejection() {
  isRejectModalOpen.value = false
  task.value.status = 'hold'
  toastRef.value?.show(
    'Tugas Ditolak (Exemption)',
    `Penolakan kargo ${task.value.awb} berhasil dicatat. Tugas dialihkan ke Satria cadangan di Hub Cawang.`
  )
}

// Tab 2 Actions
function handleConfirmArrived() {
  hasArrived.value = true
  arrivedTime.value = new Date().toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
  toastRef.value?.show(
    'Status Diperbarui: ARRIVED',
    `Kendaraan Satria telah tiba di lokasi ${task.value.destination_name || 'RS Medika Sehat Jakarta'}. Siap lanjut ke serah terima fisik.`
  )
}

function handleGoHandover() {
  handleTabChange(4)
}

function handleOpenIssueForm() {
  handleTabChange(3)
}

// Tab 3 Actions
const isHeld = ref(true)
const isReturning = ref(false)

function handleIssueSubmit(payload: { category: string; description: string }) {
  isHeld.value = true
  task.value.status = 'hold'
  task.value.status_label = 'Ditahan (HELD)'
  toastRef.value?.show(
    'Laporan Kendala Terkirim!',
    `AWB ${task.value.awb} berhasil dikunci ke status HELD. Menunggu instruksi resmi Admin QA.`
  )
}

function handleConfirmReturn() {
  isReturning.value = true
  task.value.status = 'hold'
  toastRef.value?.show(
    'Instruksi Retur Dikonfirmasi!',
    `Status pengiriman diperbarui menjadi returning. Rute navigasi diarahkan kembali ke Hub Cawang.`
  )
}

// Tab 4 Actions
function handleDownloadBapPdf() {
  toastRef.value?.show(
    'Mengunduh Salinan Dokumen BAP',
    `Memproses file BAP-PHA-20261014-8891.pdf terenkripsi SHA-256 untuk resi ${task.value.awb}...`
  )
}

function handleShareWhatsappDispatcher() {
  const text = encodeURIComponent(
    `Laporan Tugas Selesai (DELIVERED):\nAWB: ${task.value.awb}\nPenerima: Apt. Siti Rahma, S.Farm (RS Medika Sehat Jakarta)\nSuhu Serah Terima: 4.4°C\nSegel Fisik: SL-8891 (Utuh)\nWaktu Selesai: 14 Okt 2026, 15:18 WIB\ne-POD: BAP-PHA-20261014-8891 (Valid CDOB BPOM)`
  )
  window.open(`https://wa.me/6281288889999?text=${text}`, '_blank')
}
</script>

<template>
  <div class="bg-surface font-body-md text-on-surface antialiased min-h-screen flex flex-col justify-between">
    <!-- Fixed Top Navbar with 4 Execution Tabs & Lock Protection -->
    <CourierTaskDetailNavbar
      :active-tab="activeTab"
      :awb="task.awb"
      :is-accepted="isAccepted"
      @tab-change="handleTabChange"
      @locked-tab-click="handleLockedTabClick"
    />

    <!-- Main Workspace Content -->
    <main class="w-full pt-20 flex-1 bg-[#F8FAFC]">
      <!-- TAB 1: TUGAS MASUK (Matching Exact Revised Specification) -->
      <div v-if="activeTab === 1" class="max-w-5xl mx-auto py-8 px-4 sm:px-6 space-y-6">
        <!-- 1. Top Header -->
        <div class="space-y-1.5">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-50 border border-pink-200/70 text-[#D81E5B] text-xs font-bold tracking-wider uppercase">
            <span class="material-symbols-outlined text-sm">verified</span>
            <span>✦ SATRIA PHARMA COLD-CHAIN DISPATCH</span>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">
            1. Tugas Masuk &amp; Konfirmasi Penugasan
          </h1>
          <p class="text-sm text-slate-500">
            Tinjau spesifikasi kargo medis berpendingin dan konfirmasi kesiapan pengantaran.
          </p>
        </div>

        <!-- 2. Vibrant Magenta Urgency Priority Banner -->
        <div class="w-full rounded-2xl p-5 shadow-sm bg-gradient-to-r from-[#D81E5B] to-[#AD1457] text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-white text-2xl">notifications_active</span>
            </div>
            <div>
              <div class="text-[11px] uppercase tracking-wider text-pink-100 font-bold font-mono">• STATUS PENUGASAN</div>
              <h2 class="text-base md:text-lg font-bold text-white tracking-wide">
                PENUGASAN BARU MENUNGGU RESPON SATRIA — AWB #{{ task.awb }}
              </h2>
              <p class="text-xs text-pink-100 mt-0.5">
                1 Paket Cold-Chain Kritis Siap Diambil di {{ task.origin_detail || 'Hub Cawang Staging #02' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap self-end md:self-auto">
            <span class="bg-amber-400 text-slate-900 font-bold px-3 py-1.5 rounded-full text-xs shadow-sm flex items-center gap-1.5">
              <span class="material-symbols-outlined text-sm">timer</span>
              <span>Batas Respon: <strong class="font-mono">{{ countdownDisplay }}</strong></span>
            </span>
            <span class="bg-white/20 backdrop-blur-sm border border-white/30 text-white font-mono text-xs px-3 py-1.5 rounded-full font-semibold">
              ColdBox {{ task.coldbox_id || 'CBX-882' }}
            </span>
            <span class="bg-emerald-500/90 text-white text-xs px-3 py-1.5 rounded-full font-bold flex items-center gap-1 shadow-sm">
              <span class="material-symbols-outlined text-sm">ac_unit</span>
              <span>Controlled 2.0°C - 8.0°C</span>
            </span>
          </div>
        </div>

        <!-- 3. 4-Stat Cards Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <!-- Stat 1: Suhu Hub IoT -->
          <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-2xl">device_thermostat</span>
            </div>
            <div>
              <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider block">Suhu Hub IoT</span>
              <span class="text-xl font-bold font-mono text-emerald-600">{{ task.temp_actual || 4.2 }}°C</span>
            </div>
          </div>

          <!-- Stat 2: Segel Fisik -->
          <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-pink-50 text-[#D81E5B] flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-2xl">lock</span>
            </div>
            <div>
              <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider block">Segel Fisik</span>
              <span class="text-sm font-bold font-mono text-slate-800">{{ task.seal_str || 'SL-8891 (Terkunci)' }}</span>
            </div>
          </div>

          <!-- Stat 3: Volume & Berat -->
          <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-2xl">package_2</span>
            </div>
            <div>
              <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider block">Volume &amp; Berat</span>
              <span class="text-sm font-bold text-slate-800">{{ task.weight_str || '2 Koli • 4.8 kg' }}</span>
            </div>
          </div>

          <!-- Stat 4: Kepatuhan -->
          <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-2xl">verified_user</span>
            </div>
            <div>
              <span class="text-[11px] text-slate-400 uppercase font-bold tracking-wider block">Kepatuhan</span>
              <span class="text-sm font-bold text-slate-800">Valid CPOB &amp; GDP</span>
            </div>
          </div>
        </div>

        <!-- 4. 2-Col Grid: Route & Cargo Specs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Col 1: Rute & Lokasi Pengantaran -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-6">
            <div class="space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                  <span class="material-symbols-outlined text-[#D81E5B]">alt_route</span>
                  <span>Rute &amp; Lokasi Pengantaran</span>
                </h2>
                <span class="text-xs font-mono font-bold text-[#D81E5B] bg-pink-50 px-2.5 py-1 rounded-full">
                  {{ task.route_eta_str || 'ETA: 42 MENIT' }}
                </span>
              </div>

              <div class="space-y-5 relative pl-6 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                <!-- Origin -->
                <div class="relative">
                  <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-white shadow-sm"></span>
                  <div class="text-[11px] font-mono font-bold text-emerald-600 uppercase">Origin (Hub Cawang)</div>
                  <div class="text-sm font-bold text-slate-800">{{ task.origin_name || 'Hub Gudang Cawang, Jakarta Timur' }}</div>
                  <p class="text-xs text-slate-500 mt-0.5">
                    {{ task.origin_detail || 'Staging Cold Room Area #02 (Pintu Muat C-4, Dermaga Dingin)' }}
                  </p>
                </div>

                <!-- Destination -->
                <div class="relative">
                  <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-[#D81E5B] ring-4 ring-white shadow-sm"></span>
                  <div class="text-[11px] font-mono font-bold text-[#D81E5B] uppercase">Destination (RS Medika Sehat)</div>
                  <div class="text-sm font-bold text-slate-800">{{ task.destination_name || 'RS Medika Sehat Jakarta' }}</div>
                  <p class="text-xs text-slate-600 font-medium">{{ task.destination_detail || 'Jl. Raya Salemba No. 4, Jakarta Pusat' }}</p>
                  <div class="mt-2 p-2.5 bg-slate-50 border border-slate-200/70 rounded-lg">
                    <span class="text-[11px] text-slate-400 font-mono block">Titik Serah Terima Presisi:</span>
                    <span class="text-xs text-[#D81E5B] font-semibold">
                      {{ task.handover_person || 'Instalasi Farmasi Khusus Vaksin Gd. B Lt. 1 (Apt. Nurul Hidayah, S.Farm)' }}
                    </span>
                  </div>
                </div>

                <!-- SLA -->
                <div class="relative">
                  <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-amber-500 ring-4 ring-white shadow-sm"></span>
                  <div class="text-[11px] font-mono font-bold text-amber-600 uppercase">Target Waktu SLA</div>
                  <div class="text-sm font-bold text-slate-800">{{ task.scheduled_at_str || 'Hari ini, 14 Okt 2026 • Maks 15:30 WIB' }}</div>
                  <span class="inline-flex items-center gap-1 text-[11px] text-rose-600 font-semibold mt-0.5">
                    <span class="material-symbols-outlined text-xs">schedule</span>
                    <span>{{ task.sla_warning || 'SLA Kritis Rumah Sakit: Tidak boleh terlambat > 10 menit' }}</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Route Map Visual -->
            <div class="w-full h-36 rounded-xl overflow-hidden relative shadow-inner border border-slate-200">
              <div class="w-full h-full bg-slate-100 flex items-center justify-center relative">
                <img
                  class="w-full h-full object-cover"
                  alt="Satellite GPS route map"
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuCk7UZxFaF25f3C8toOLb_iJhh3eUwiaE3mvdrR1qtdg8pgh93tc0zjqqxPdVvRJIupcf070kfpOM01Z8OvQhQZhkcHAeT1EiLVeh65Ys49Zj6UcBnhLbUp1IJNAYG-pqPzn9wlQ1OnRRqUGmSBsH0BfuVSYbaar69mOIO-UQVSJoXNpO4WWKkaUt0wJnhEY2ILujm2vI2ew6Lu1IMIMu57zkCLW3BTzUJKejhE2Pmx7dzMt7tMfAsx9A"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent flex items-end p-3">
                  <div class="flex items-center justify-between w-full text-white">
                    <div class="flex items-center gap-1.5 text-xs font-semibold">
                      <span class="material-symbols-outlined text-emerald-400 text-sm">navigation</span>
                      <span>{{ task.route_traffic_note || 'Jalur Prioritas Bebas Macet (Via Tol Cawang - Pramuka)' }}</span>
                    </div>
                    <span class="text-[11px] bg-black/50 px-2 py-0.5 rounded backdrop-blur-sm font-mono">
                      {{ task.route_distance_km || 11.4 }} km
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Col 2: Spesifikasi Muatan & CPOB -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-6">
            <div class="space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                  <span class="material-symbols-outlined text-[#D81E5B]">clinical_notes</span>
                  <span>Spesifikasi Muatan &amp; CPOB</span>
                </h2>
                <span class="text-xs font-mono font-bold text-[#D81E5B] bg-pink-50 px-2.5 py-1 rounded-full">
                  BATCH: {{ task.batch_number || 'MN-2026-X8' }}
                </span>
              </div>

              <!-- Commodity & Packaging -->
              <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                <span class="text-[11px] font-mono text-slate-400 uppercase block font-semibold">Komoditas &amp; Kemasan</span>
                <div class="text-sm font-bold text-slate-800">{{ task.commodity_name || 'Vaksin Meningococcal Konjugat (MenACWY)' }}</div>
                <p class="text-xs text-slate-500 mt-0.5">{{ task.weight_str || '2 Koli (Insulated Shipper Box Khusus Vaksin, 4.8 kg)' }}</p>
              </div>

              <!-- Temperature Controlled Range Bar -->
              <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-[11px] font-mono text-slate-400 uppercase font-semibold">Rentang Suhu Terkontrol (CPOB GDP)</span>
                  <span class="text-xs font-mono font-bold text-emerald-600">2.0°C – 8.0°C</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 relative overflow-hidden flex">
                  <div class="w-1/4 bg-rose-200"></div>
                  <div class="w-2/4 bg-emerald-300 relative flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-emerald-600 shadow-md animate-ping"></div>
                  </div>
                  <div class="w-1/4 bg-rose-200"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-500">
                  <span>0.0°C (Beku)</span>
                  <span class="text-emerald-700 font-bold">Titik Aktual: {{ task.temp_actual || 4.2 }}°C</span>
                  <span>10.0°C (Rusak)</span>
                </div>
              </div>

              <!-- Handling Instructions -->
              <div class="p-3.5 bg-pink-50/60 border border-pink-100 rounded-xl">
                <span class="text-[11px] font-mono text-[#D81E5B] uppercase block font-bold">Instruksi Penanganan Khusus</span>
                <div class="flex items-start gap-2 mt-1.5">
                  <span class="material-symbols-outlined text-[#D81E5B] shrink-0 text-base">warning</span>
                  <p class="text-xs text-slate-700 leading-snug">
                    {{ task.handling_instructions || 'Wajib menggunakan Insulated Cooler Box aktif dengan 4 blok icepack PCM chilled. Dilarang membalik posisi boks (tanda panah selalu menghadap atas), hindari guncangan ekstrem dan sinar matahari langsung.' }}
                  </p>
                </div>
              </div>

              <!-- Inspector Badge -->
              <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between">
                <div>
                  <span class="text-[11px] font-mono text-slate-400 uppercase block font-semibold">Pemeriksa Cold Hub (Inspector)</span>
                  <div class="text-xs font-bold text-slate-800">
                    {{ task.inspector_name || 'Hendra Setiawan' }} • ID: {{ task.inspector_id_code || 'CHK-092' }}
                  </div>
                  <div class="text-[11px] text-emerald-600 font-medium">
                    {{ task.inspector_status || 'Lolos Verifikasi QC Fisik & Kalibrasi Sensor' }}
                  </div>
                </div>
                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                  <span class="material-symbols-outlined text-lg">verified</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. Checklist Pra-Jalan Satria Pharma (Pre-Trip Checklist) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
            <div>
              <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">fact_check</span>
                <span>Checklist Pra-Jalan Satria Pharma (Pre-Trip Checklist)</span>
              </h3>
              <p class="text-xs text-slate-500">
                Selesaikan seluruh poin verifikasi sebelum melakukan konfirmasi tugas di sistem.
              </p>
            </div>
            <span
              class="text-xs px-3 py-1 font-mono rounded-full font-bold self-start md:self-auto border transition-colors"
              :class="checklistCount === 3
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-slate-100 text-slate-700 border-slate-200'"
            >
              {{ checklistCount }} / 3 Terverifikasi
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
            <!-- Item 1 -->
            <label class="group relative flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 hover:bg-pink-50/40 border border-slate-200/80 transition-all cursor-pointer">
              <input
                v-model="check1"
                class="mt-0.5 w-4 h-4 rounded text-[#D81E5B] focus:ring-[#D81E5B] accent-[#D81E5B]"
                type="checkbox"
              />
              <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-800 group-hover:text-[#D81E5B] transition-colors">
                  1. Segel &amp; Kunci Fisik Box
                </span>
                <span class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                  Active Cooler Box terpasang kokoh &amp; tersegel {{ task.seal_str ? task.seal_str.split(' ')[0] : 'SL-8891' }} utuh tanpa retak.
                </span>
              </div>
            </label>

            <!-- Item 2 -->
            <label class="group relative flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 hover:bg-pink-50/40 border border-slate-200/80 transition-all cursor-pointer">
              <input
                v-model="check2"
                class="mt-0.5 w-4 h-4 rounded text-[#D81E5B] focus:ring-[#D81E5B] accent-[#D81E5B]"
                type="checkbox"
              />
              <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-800 group-hover:text-[#D81E5B] transition-colors">
                  2. Icepack PCM Dingin
                </span>
                <span class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                  PCM conditioning stabil, suhu chamber terbaca {{ task.temp_actual || 4.2 }}°C di display.
                </span>
              </div>
            </label>

            <!-- Item 3 -->
            <label class="group relative flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 hover:bg-pink-50/40 border border-slate-200/80 transition-all cursor-pointer">
              <input
                v-model="check3"
                class="mt-0.5 w-4 h-4 rounded text-[#D81E5B] focus:ring-[#D81E5B] accent-[#D81E5B]"
                type="checkbox"
              />
              <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-800 group-hover:text-[#D81E5B] transition-colors">
                  3. Telemetri IoT Sensirion
                </span>
                <span class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                  Beacon Bluetooth &amp; GSM logger terhubung, GPS aktif tiap 30 detik.
                </span>
              </div>
            </label>
          </div>
        </div>

        <!-- 6. Bottom Action Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col-reverse sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-2 text-slate-500">
            <span class="material-symbols-outlined text-sm text-emerald-600">verified</span>
            <span class="text-xs font-mono uppercase font-semibold">Kepatuhan Good Distribution Practice (BPOM 2026)</span>
          </div>

          <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <!-- Tolak Tugas -->
            <button
              type="button"
              @click="openRejectModal"
              class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold text-sm transition-all flex items-center justify-center gap-2 border border-rose-200 shadow-sm active:scale-[0.98] cursor-pointer"
            >
              <span class="material-symbols-outlined text-base">close</span>
              <span>✕ Tolak Tugas (Input Alasan Penolakan)</span>
            </button>

            <!-- Terima Tugas -->
            <button
              type="button"
              @click="handleAcceptTask"
              :disabled="isProcessing || isAccepted"
              class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#D81E5B] hover:bg-[#C2185B] text-white font-bold text-sm shadow-sm transition-all flex items-center justify-center gap-2.5 active:scale-[0.98] cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <span class="material-symbols-outlined text-lg">check_circle</span>
              <span v-if="isProcessing">Memproses Konfirmasi...</span>
              <span v-else-if="isAccepted">✓ Tugas Telah Diterima (Accepted)</span>
              <span v-else>✓ Terima Tugas Pengantaran (status -> accepted)</span>
            </button>
          </div>
        </div>

        <!-- 7. Success Notification Banner (When Accepted) -->
        <div
          v-if="isAccepted"
          class="w-full bg-emerald-600 text-white p-6 rounded-2xl shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all animate-fadeIn"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shrink-0">
              <span class="material-symbols-outlined text-2xl">check</span>
            </div>
            <div>
              <h4 class="text-base font-bold text-white">Tugas Berhasil Diterima! (Status: Accepted)</h4>
              <p class="text-xs text-emerald-100 mt-0.5">
                Data tersinkron ke Pengantaran &amp; Monitor. Silakan ambil paket di Staging Room #02 dan lakukan scan barcode.
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="handleTabChange(2)"
            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white text-emerald-700 font-bold text-xs shadow hover:bg-emerald-50 transition-all cursor-pointer shrink-0"
          >
            Mulai Navigasi &amp; Monitor →
          </button>
        </div>

        <!-- 8. Reject Modal -->
        <div
          v-if="isRejectModalOpen"
          class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        >
          <div class="bg-white rounded-2xl max-w-lg w-full p-6 md:p-8 shadow-2xl space-y-6 border border-slate-200">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                  <span class="material-symbols-outlined">warning</span>
                </div>
                <div>
                  <h4 class="text-lg font-bold text-slate-900">Penolakan Tugas Kritis</h4>
                  <span class="text-xs font-mono text-slate-500">ID: {{ task.awb }} • Cold-Chain Exemption</span>
                </div>
              </div>
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                @click="closeRejectModal"
              >
                <span class="material-symbols-outlined">close</span>
              </button>
            </div>

            <div class="space-y-4">
              <p class="text-xs text-slate-600 leading-relaxed">
                Penolakan tugas vaksin memerlukan verifikasi alasan operasional untuk dialihkan secara otomatis ke Satria cadangan di Hub Cawang.
              </p>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Kategori Alasan Penolakan:</label>
                <select
                  v-model="rejectCategory"
                  class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#D81E5B]"
                >
                  <option value="equipment_failure">Cooler Box / IoT Logger Tidak Berfungsi Saat Cek Fisik</option>
                  <option value="vehicle_issue">Kendaraan Mengalami Kendala Teknis / Mesin / Kelistrikan</option>
                  <option value="shift_overtime">Mendekati Batas Maksimal Jam Operasional CPOB</option>
                  <option value="capacity_limit">Kapasitas Carrier Bag Motor Penuh / Melebihi Beban Aman</option>
                  <option value="other">Lainnya (Tuliskan Keterangan)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan Detail (Wajib):</label>
                <textarea
                  v-model="rejectNotes"
                  class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#D81E5B] placeholder:text-slate-400"
                  placeholder="Contoh: Baterai sensor logger Sensirion di bawah 15% atau segel tamper terindikasi retak..."
                  rows="3"
                ></textarea>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <button
                type="button"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer"
                @click="closeRejectModal"
              >
                Batal
              </button>
              <button
                type="button"
                class="px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold cursor-pointer"
                @click="submitRejection"
              >
                Kirim Penolakan Tugas
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: PENGANTARAN & MONITOR -->
      <div v-else-if="activeTab === 2" class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col w-full space-y-6">
          <CourierDeliveryStepper
            :task="task"
            :has-arrived="hasArrived"
            :arrived-time="arrivedTime"
            @confirm-arrived="handleConfirmArrived"
            @go-handover="handleGoHandover"
          />

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
              <CourierDeliveryMapCard
                speed="28.4 km/h"
                distance-remaining="4.2 KM"
                current-position="Tol Cawang KM 12 → Exit Kuningan/Salemba"
                traffic-status="Trafik: Lancar Terkendali"
                :destination-query="task.destination_name || 'RS Medika Sehat Jakarta Salemba'"
              />

              <CourierDeliveryRecipientCard
                :facility-name="task.destination_name || 'RS Medika Sehat Jakarta'"
                :facility-address="task.destination_detail || 'Gedung Farmasi & Logistik Medik, Lantai 1 (Pintu Masuk Loading Dock Barat)'"
                apj-name="Apt. Siti Rahma, S.Farm"
                apj-sip="SIP: 19880412/SIPA-DKI/2022"
                api-phone="0812-9842-1100"
              />
            </div>

            <div class="space-y-6">
              <CourierDeliveryTelemetryPanel
                :current-temp="4.5"
                probe-id="SENS-IOT-771"
                :battery-pct="96"
                protocol="BLE 5.2 / CPOB"
                ping-cycle="Tiap 30 Detik"
              />

              <CourierDeliveryIssueBanner
                @open-issue-form="handleOpenIssueForm"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: LAPOR KENDALA -->
      <div v-else-if="activeTab === 3" class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col w-full space-y-6">
          <CourierIssueNoticeBanner />

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <CourierIssueReportForm
              :awb="task.awb"
              :is-held="isHeld"
              @submit-issue="handleIssueSubmit"
            />

            <CourierIssueDecisionCard
              :awb="task.awb"
              :is-returning="isReturning"
              @confirm-return="handleConfirmReturn"
            />
          </div>

          <CourierIssueAuditLogStrip />
        </div>
      </div>

      <!-- TAB 4: SERAH TERIMA & LAPORAN -->
      <div v-else-if="activeTab === 4" class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex flex-col w-full space-y-6">
          <CourierHandoverBanner
            :awb="task.awb"
            facility-name="RS Medika Sehat Jakarta"
            completed-time="14 Okt 2026, 15:18 WIB"
          />

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
              <CourierHandoverEpodCard
                :awb="task.awb"
                recipient-name="Apt. Siti Rahma, S.Farm"
                recipient-role="Kepala Depo Farmasi & Logistik Medik"
                sipa-num="SIPA: 19880412/SIPA-DKI/2022"
                completion-date="14 Okt 2026, 15:18 WIB"
                handover-temp="4.4°C"
                @download-pdf="handleDownloadBapPdf"
                @share-whatsapp="handleShareWhatsappDispatcher"
              />

              <CourierHandoverCargoSummaryCard
                :commodity="task.item_title"
                :batch-number="task.batch_number"
                :box-id="task.coldbox_id || 'CBX-882'"
                :seal-number="task.seal_str || 'SL-8891'"
                package-weight="2 Koli • 4.8 kg"
              />
            </div>

            <div class="space-y-6">
              <CourierHandoverTimelineCard
                origin-time="14:10 WIB (Dispatched)"
                arrived-time="14:52 WIB (Arrived)"
                verified-time="15:18 WIB (Accepted by APJ)"
              />

              <CourierHandoverReportCard
                @download-report="handleDownloadBapPdf"
              />
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Global Courier Footer -->
    <CourierFooter
      :is-accepted="isAccepted"
      @tab-change="handleTabChange"
      @locked-tab-click="handleLockedTabClick"
    />

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

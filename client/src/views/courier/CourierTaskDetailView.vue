<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mockCourierTasks } from '../../data/mockShipments'
import type { CourierTaskItem } from '../../types/shipment'
import CourierTaskDetailNavbar from '../../components/courier/CourierTaskDetailNavbar.vue'
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
const activeTab = ref<number>(1)

// Synchronize tab with route query with lock protection
watch(
  () => route.query.tab,
  newTab => {
    if (newTab) {
      const tabNum = Number(newTab)
      if (tabNum !== 1 && !isAccepted.value) {
        activeTab.value = 1
        router.replace({ query: { ...route.query, tab: '1' } })
        handleLockedTabClick(tabNum)
      } else {
        activeTab.value = tabNum
      }
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
  if (!isAccepted.value && Number(route.query.tab) > 1) {
    activeTab.value = 1
    router.replace({ query: { ...route.query, tab: '1' } })
    setTimeout(() => {
      handleLockedTabClick(Number(route.query.tab) || 2)
    }, 400)
  } else if (route.query.tab) {
    activeTab.value = Number(route.query.tab)
  }

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

// Tab 3 State & Actions
const selectedIssueType = ref<'seal' | 'temperature' | 'packaging' | 'recipient_unavailable'>('seal')
const issueDescription = ref(
  'Saat tiba dan akan diserahterimakan di instalasi farmasi RS Medika Sehat, ditemukan segel plastik tamper-evident nomor SL-8891 robek sebagian dan kait boks renggang saat proses penurunan dari reefer van.'
)
const isHeld = ref(true)
const isReturning = ref(false)

function handleIssueSubmitDirect() {
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
  task.value.status_label = 'Retur (returning)'
  toastRef.value?.show(
    'Instruksi Retur Dikonfirmasi!',
    `Status pengiriman diperbarui menjadi returning. Rute navigasi diarahkan kembali ke Hub Cawang.`
  )
}

// Tab 4 State & Actions
const handoverMode = ref<'normal' | 'return'>('normal')

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
      <div v-else-if="activeTab === 2" class="max-w-5xl mx-auto py-8 px-4 sm:px-6 space-y-6">
        <!-- Hero Header -->
        <div class="space-y-1.5">
          <div>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#FCE7F3] text-[#D81E5B] text-xs font-bold tracking-wide uppercase">
              ✦ SATRIA PHARMA COLD-CHAIN DISPATCH
            </span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
            2. Pengantaran &amp; Monitor Telemetri
          </h1>
          <p class="text-slate-500 text-sm sm:text-base">
            Navigasi rute langsung armada berpendingin dan pemantauan sensor suhu kargo secara real-time.
          </p>
        </div>

        <!-- Stepper & Sequential Workflow Control -->
        <section class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Step 1: Completed -->
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">check</span>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-bold text-emerald-900">1. Paket Berangkat</span>
                  <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-emerald-200 text-emerald-800 uppercase">depart</span>
                </div>
                <p class="text-xs text-emerald-700 mt-0.5">Diserahkan &amp; dicek Hub Cawang</p>
                <span class="text-xs font-semibold text-emerald-600 mt-1 block">13:45 WIB • Terverifikasi</span>
              </div>
            </div>

            <!-- Step 2: Active Primary CTA (When not arrived) -->
            <button
              v-if="!hasArrived"
              type="button"
              id="arrive-cta"
              @click="handleConfirmArrived"
              class="relative group bg-[#D81E5B] hover:bg-[#b01447] text-white rounded-xl p-4 text-left shadow-sm transition-all cursor-pointer border border-[#D81E5B]"
            >
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-white text-[#D81E5B] flex items-center justify-center shrink-0 font-bold text-sm shadow-sm relative">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#D81E5B] animate-ping absolute"></span>
                  2
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-white">2. Tiba di RS Penerima</span>
                    <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-white/20 text-white uppercase">arrive</span>
                  </div>
                  <p class="text-xs text-pink-100 mt-0.5">Tekan saat kendaraan parkir di RS Medika</p>
                  <div class="mt-2.5 flex items-center gap-1.5 text-xs font-bold bg-white text-[#D81E5B] px-3 py-1.5 rounded-lg w-fit shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">touch_app</span>
                    <span>Konfirmasi Tiba (Status → Arrived)</span>
                  </div>
                </div>
              </div>
            </button>

            <!-- Step 2: Arrived state -->
            <div
              v-else
              class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex items-start gap-3"
            >
              <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">check</span>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-bold text-emerald-900">2. Tiba di RS Penerima</span>
                  <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-emerald-200 text-emerald-800 uppercase">arrived</span>
                </div>
                <p class="text-xs text-emerald-700 mt-0.5">Kendaraan parkir di RS Medika</p>
                <span class="text-xs font-semibold text-emerald-600 mt-1 block">{{ arrivedTime }} WIB • Terverifikasi</span>
              </div>
            </div>

            <!-- Step 3: Pending or Handover CTA -->
            <button
              v-if="hasArrived"
              type="button"
              @click="handleGoHandover"
              class="relative group bg-[#D81E5B] hover:bg-[#b01447] text-white rounded-xl p-4 text-left shadow-sm transition-all cursor-pointer border border-[#D81E5B]"
            >
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-white text-[#D81E5B] flex items-center justify-center shrink-0 font-bold text-sm shadow-sm relative">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#D81E5B] animate-ping absolute"></span>
                  3
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-white">3. Serah Terima Fisik</span>
                    <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-white/20 text-white uppercase">handover</span>
                  </div>
                  <p class="text-xs text-pink-100 mt-0.5">Scan barcode obat &amp; tanda tangan digital APJ</p>
                  <div class="mt-2.5 flex items-center gap-1.5 text-xs font-bold bg-white text-[#D81E5B] px-3 py-1.5 rounded-lg w-fit shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">touch_app</span>
                    <span>Buka Serah Terima &amp; Laporan →</span>
                  </div>
                </div>
              </div>
            </button>
            <div
              v-else
              class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-start gap-3 opacity-60"
            >
              <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 font-bold text-xs">
                3
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-bold text-slate-700">3. Serah Terima Fisik</span>
                  <span class="text-[11px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 uppercase">handover</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Scan barcode obat &amp; tanda tangan digital APJ</p>
                <span class="text-xs text-slate-400 mt-1 block">Menunggu status Tiba</span>
              </div>
            </div>
          </div>
        </section>

        <!-- 2-Column Responsive Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Left Column (7 cols): Navigation View & Contact Details -->
          <div class="lg:col-span-7 space-y-6">
            <!-- Active Route Map Visualizer -->
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm flex flex-col">
              <div class="p-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[#D81E5B]">navigation</span>
                  <span class="font-bold text-slate-800 text-sm">Panduan Rute Satria Pharma</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 text-xs font-bold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live GPS 4G
                  </span>
                </div>
              </div>

              <!-- Map View Canvas Container -->
              <div class="relative w-full h-80 bg-slate-100 overflow-hidden">
                <div
                  class="absolute inset-0 bg-cover bg-center"
                  style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAt4s7AT-VDyn2yizRUGATaA0KAsMP1TqsQUaMeOiyxtFVP467FsEIypFTgHWXLe_lFDzCxTPmwzildK3eBBDLBm551nU3x96JlQeTxkAfoW5DuhG4cb2ZFB-eJ0MT9zjAjczorGPwAZbUrooK_rRQJXh17rxF5yIecEN_lvAoKKI9Wz-7QMFAVsSyFHOiUg4dGG7r4-_HMDAdPxco9SJBTmkaOzdyToqNnHy0lDj706dOlqYGlsEw-uQ')"
                ></div>
                <!-- Overlay details -->
                <div class="absolute inset-0 bg-slate-900/10 p-4 flex flex-col justify-between pointer-events-none">
                  <div class="flex items-center gap-3 self-start bg-white/95 backdrop-blur-md px-4 py-2.5 rounded-xl shadow-md text-slate-800">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">speed</span>
                    <div>
                      <span class="text-[11px] text-slate-500 block leading-none font-medium">Kecepatan Armada</span>
                      <span class="text-sm font-bold text-slate-800">28.4 km/h</span>
                    </div>
                    <div class="h-6 w-px bg-slate-200 mx-1"></div>
                    <div>
                      <span class="text-[11px] text-slate-500 block leading-none font-medium">Jarak Tersisa</span>
                      <span class="text-sm font-bold text-[#D81E5B]">4.2 KM</span>
                    </div>
                  </div>

                  <div class="bg-white/95 backdrop-blur-md p-3.5 rounded-xl shadow-md flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-[#FCE7F3] text-[#D81E5B] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">explore</span>
                      </div>
                      <div>
                        <span class="text-[10px] uppercase font-bold text-slate-500">Posisi Saat Ini</span>
                        <p class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">Tol Cawang KM 12 → Exit Kuningan/Salemba</p>
                      </div>
                    </div>
                    <span class="hidden sm:inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-md text-xs font-semibold">Trafik: Lancar Terkendali</span>
                  </div>
                </div>
              </div>

              <!-- Direct Actions -->
              <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-white border-t border-slate-100">
                <a
                  class="flex items-center justify-center gap-2 px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-sm rounded-xl transition-colors border border-slate-200/70"
                  href="https://maps.google.com"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  <span class="material-symbols-outlined text-[20px] text-[#D81E5B]">map</span>
                  <span>Buka Google Maps</span>
                </a>
                <a
                  class="flex items-center justify-center gap-2 px-4 py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-sm rounded-xl transition-colors border border-slate-200/70"
                  href="https://waze.com"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  <span class="material-symbols-outlined text-[20px] text-emerald-600">alt_route</span>
                  <span>Buka Navigasi Waze</span>
                </a>
              </div>
            </div>

            <!-- Recipient Contact & Pharmacy Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
              <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                  <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Detail Lokasi Tujuan</span>
                  <h3 class="text-lg font-bold text-slate-900 mt-0.5">{{ task.destination_name || 'RS Medika Sehat Jakarta' }}</h3>
                  <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ task.destination_detail || 'Gedung Farmasi & Logistik Medik, Lantai 1 (Pintu Masuk Loading Dock Barat)' }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">Priority Hospital</span>
              </div>

              <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="w-11 h-11 rounded-full bg-[#FCE7F3] text-[#D81E5B] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]">medical_services</span>
                  </div>
                  <div>
                    <span class="text-xs text-slate-500 block">Apoteker Penanggung Jawab (APJ Penerima)</span>
                    <p class="text-sm font-bold text-slate-900">Apt. Siti Rahma, S.Farm</p>
                    <p class="text-xs text-slate-500 font-mono">SIP: 19880412/SIPA-DKI/2022 • 0812-9842-1100</p>
                  </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                  <a
                    class="flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-lg transition-colors border border-slate-200 shadow-sm"
                    href="tel:081298421100"
                  >
                    <span class="material-symbols-outlined text-[16px]">call</span>
                    <span>Panggil</span>
                  </a>
                  <a
                    class="flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 text-white hover:bg-emerald-700 font-bold text-xs rounded-lg transition-colors shadow-sm"
                    href="https://wa.me/6281298421100"
                    rel="noopener noreferrer"
                    target="_blank"
                  >
                    <span class="material-symbols-outlined text-[16px]">chat</span>
                    <span>WhatsApp APJ</span>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column (5 cols): Running Monitor Telemetry -->
          <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-5">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                  </span>
                  <span class="text-xs font-bold text-slate-800">Running Monitor: AKTIF</span>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold">Sensor Terkoneksi</span>
              </div>
              <p class="text-xs text-slate-500">Data IoT &amp; GPS dipancarkan setiap 30 detik secara otomatis ke Control Tower Satria Pharma &amp; AI Excursion Engine.</p>

              <!-- Temperature Hero Readout -->
              <div class="bg-emerald-50/70 border border-emerald-100 p-5 rounded-xl text-center flex flex-col items-center justify-center">
                <div class="flex items-center gap-1 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-1">
                  <span class="material-symbols-outlined text-[16px]">thermostat</span>
                  <span>Suhu Probe Box Farmasi</span>
                </div>
                <div class="text-5xl font-bold text-emerald-700 tracking-tight my-1">4.5°C</div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  <span>Controlled Chilled (Ambang 2.0°C - 8.0°C)</span>
                </span>
              </div>

              <!-- Stability History Sparkline -->
              <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-500">
                  <span>Tren Stabilitas (60 Menit Terakhir)</span>
                  <span class="font-bold text-emerald-700">Stabil (+0.3°C)</span>
                </div>
                <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl">
                  <div class="h-16 w-full flex items-end">
                    <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 300 80">
                      <rect class="text-emerald-500/10" fill="currentColor" height="60" rx="4" width="300" x="0" y="10"></rect>
                      <line class="text-emerald-600/30" stroke="currentColor" stroke-dasharray="3,3" stroke-width="1" x1="0" x2="300" y1="15" y2="15"></line>
                      <line class="text-emerald-600/30" stroke="currentColor" stroke-dasharray="3,3" stroke-width="1" x1="0" x2="300" y1="65" y2="65"></line>
                      <polyline class="text-emerald-600" fill="none" points="10,50 80,47 160,43 240,40 290,38" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></polyline>
                      <circle class="text-emerald-600" cx="10" cy="50" fill="currentColor" r="4"></circle>
                      <circle class="text-emerald-600" cx="80" cy="47" fill="currentColor" r="4"></circle>
                      <circle class="text-emerald-600" cx="160" cy="43" fill="currentColor" r="4"></circle>
                      <circle class="text-emerald-600" cx="240" cy="40" fill="currentColor" r="4"></circle>
                      <circle class="text-emerald-600" cx="290" cy="38" fill="currentColor" r="5"></circle>
                    </svg>
                  </div>
                  <div class="flex justify-between text-[11px] text-slate-500 mt-2 pt-1 border-t border-slate-100">
                    <span>13:45 (4.2°C)</span>
                    <span>14:05 (4.3°C)</span>
                    <span>14:25 (4.4°C)</span>
                    <span class="font-bold text-slate-800">14:45 (4.5°C)</span>
                  </div>
                </div>
              </div>

              <!-- Hardware Specs -->
              <div class="grid grid-cols-2 gap-2.5 pt-1">
                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                  <span class="text-[11px] text-slate-400 block">ID Probe IoT</span>
                  <span class="text-xs font-bold text-slate-800">SENS-IOT-771</span>
                </div>
                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                  <span class="text-[11px] text-slate-400 block">Baterai Logger</span>
                  <span class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">battery_charging_full</span> 96%
                  </span>
                </div>
                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                  <span class="text-[11px] text-slate-400 block">Protokol</span>
                  <span class="text-xs font-bold text-slate-800">BLE 5.2 / CPOB</span>
                </div>
                <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-lg">
                  <span class="text-[11px] text-slate-400 block">Siklus Ping</span>
                  <span class="text-xs font-bold text-slate-800">Tiap 30 Detik</span>
                </div>
              </div>
            </div>

            <!-- Amber Alert Banner -->
            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-5 shadow-sm space-y-3">
              <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-[20px]">warning</span>
                </div>
                <div>
                  <h4 class="text-sm font-bold text-amber-950">Kendala Selama Perjalanan?</h4>
                  <p class="text-xs text-amber-900/80 mt-1 leading-relaxed">
                    Jika terjadi perubahan suhu mendadak (&gt;8.0°C), segel thermal rusak, kemasan basah, atau insiden lalu lintas.
                  </p>
                </div>
              </div>
              <button
                type="button"
                @click="handleOpenIssueForm"
                class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-white hover:bg-rose-50 text-[#D81E5B] border border-amber-200 font-bold text-xs rounded-xl shadow-sm transition-all group cursor-pointer"
              >
                <span class="material-symbols-outlined text-[16px]">report_problem</span>
                <span>Buka Form Lapor Kendala</span>
                <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: LAPOR KENDALA -->
      <div v-else-if="activeTab === 3" class="max-w-5xl mx-auto py-8 px-4 sm:px-6 space-y-6">
        <!-- Header Title & CDOB Badge -->
        <div class="flex flex-col gap-1.5">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full bg-[#FCE7F3] text-[#D81E5B] font-bold text-xs uppercase tracking-wider">
              ✦ INSIDEN COLD-CHAIN &amp; KEPATUHAN CDOB
            </span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
            3. Lapor Kendala &amp; Keputusan Admin
          </h1>
          <p class="text-slate-600 text-sm">
            Pelaporan anomali lapangan berstatus HELD dan eksekusi instruksi resmi Tim QA Control Tower.
          </p>
        </div>

        <!-- 2-Column Grid Anatomy -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
          <!-- LEFT COLUMN: Form Input Laporan Kendala Lapangan (FR-04) -->
          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col gap-5">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
              <div>
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[#D81E5B]">assignment_late</span>
                  <h2 class="font-bold text-lg text-slate-900">Form Input Laporan Kendala Lapangan</h2>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                  Tabel basis data: <span class="font-mono text-slate-700">issues</span> • Terkait AWB:
                  <span class="font-bold text-slate-800">{{ task.awb }}</span>
                </p>
              </div>
              <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-mono font-bold">FR-04</span>
            </div>

            <!-- Issue Category Selector -->
            <div class="space-y-2.5">
              <div class="flex items-center justify-between">
                <label class="text-sm font-semibold text-slate-800">
                  Kategori Kendala (<code class="text-xs font-mono text-slate-500">issues.type</code>)
                </label>
                <span class="text-xs text-[#DC2626] font-semibold">*Wajib dipilih</span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <!-- Option 1: Segel Rusak -->
                <button
                  type="button"
                  @click="selectedIssueType = 'seal'"
                  class="text-left p-3.5 rounded-xl transition-all flex items-start justify-between cursor-pointer"
                  :class="selectedIssueType === 'seal'
                    ? 'bg-red-50 border-2 border-red-500 text-red-900 shadow-xs'
                    : 'bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700'"
                >
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-bold" :class="selectedIssueType === 'seal' ? 'text-red-700' : 'text-slate-800'">
                      <span class="material-symbols-outlined text-[16px]">lock_open</span>
                      <span>Segel Rusak / Terbuka</span>
                    </div>
                    <div class="text-[11px]" :class="selectedIssueType === 'seal' ? 'text-red-500' : 'text-slate-400'">
                      Kode: <span class="font-mono">seal</span>
                    </div>
                  </div>
                  <span
                    v-if="selectedIssueType === 'seal'"
                    class="material-symbols-outlined text-red-600 text-[18px]"
                    style="font-variation-settings: 'FILL' 1;"
                  >check_circle</span>
                  <span v-else class="w-3.5 h-3.5 rounded-full border border-slate-300 mt-1"></span>
                </button>

                <!-- Option 2: Suhu Melebihi Batas -->
                <button
                  type="button"
                  @click="selectedIssueType = 'temperature'"
                  class="text-left p-3.5 rounded-xl transition-all flex items-start justify-between cursor-pointer"
                  :class="selectedIssueType === 'temperature'
                    ? 'bg-red-50 border-2 border-red-500 text-red-900 shadow-xs'
                    : 'bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700'"
                >
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-semibold" :class="selectedIssueType === 'temperature' ? 'text-red-700 font-bold' : 'text-slate-800'">
                      <span class="material-symbols-outlined text-[16px] text-amber-600">device_thermostat</span>
                      <span>Suhu Melebihi Batas (&gt;8.0°C)</span>
                    </div>
                    <div class="text-[11px]" :class="selectedIssueType === 'temperature' ? 'text-red-500' : 'text-slate-400'">
                      Kode: <span class="font-mono">temperature</span>
                    </div>
                  </div>
                  <span
                    v-if="selectedIssueType === 'temperature'"
                    class="material-symbols-outlined text-red-600 text-[18px]"
                    style="font-variation-settings: 'FILL' 1;"
                  >check_circle</span>
                  <span v-else class="w-3.5 h-3.5 rounded-full border border-slate-300 mt-1"></span>
                </button>

                <!-- Option 3: Kemasan Bocor -->
                <button
                  type="button"
                  @click="selectedIssueType = 'packaging'"
                  class="text-left p-3.5 rounded-xl transition-all flex items-start justify-between cursor-pointer"
                  :class="selectedIssueType === 'packaging'
                    ? 'bg-red-50 border-2 border-red-500 text-red-900 shadow-xs'
                    : 'bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700'"
                >
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-semibold" :class="selectedIssueType === 'packaging' ? 'text-red-700 font-bold' : 'text-slate-800'">
                      <span class="material-symbols-outlined text-[16px] text-slate-500">inventory_2</span>
                      <span>Kemasan Bocor / Rusak Fisik</span>
                    </div>
                    <div class="text-[11px]" :class="selectedIssueType === 'packaging' ? 'text-red-500' : 'text-slate-400'">
                      Kode: <span class="font-mono">packaging</span>
                    </div>
                  </div>
                  <span
                    v-if="selectedIssueType === 'packaging'"
                    class="material-symbols-outlined text-red-600 text-[18px]"
                    style="font-variation-settings: 'FILL' 1;"
                  >check_circle</span>
                  <span v-else class="w-3.5 h-3.5 rounded-full border border-slate-300 mt-1"></span>
                </button>

                <!-- Option 4: Penerima Tidak di Tempat -->
                <button
                  type="button"
                  @click="selectedIssueType = 'recipient_unavailable'"
                  class="text-left p-3.5 rounded-xl transition-all flex items-start justify-between cursor-pointer"
                  :class="selectedIssueType === 'recipient_unavailable'
                    ? 'bg-red-50 border-2 border-red-500 text-red-900 shadow-xs'
                    : 'bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700'"
                >
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-semibold" :class="selectedIssueType === 'recipient_unavailable' ? 'text-red-700 font-bold' : 'text-slate-800'">
                      <span class="material-symbols-outlined text-[16px] text-slate-500">person_off</span>
                      <span>Penerima / APJ Tidak di Tempat</span>
                    </div>
                    <div class="text-[11px]" :class="selectedIssueType === 'recipient_unavailable' ? 'text-red-500' : 'text-slate-400'">
                      Kode: <span class="font-mono">recipient_unavailable</span>
                    </div>
                  </div>
                  <span
                    v-if="selectedIssueType === 'recipient_unavailable'"
                    class="material-symbols-outlined text-red-600 text-[18px]"
                    style="font-variation-settings: 'FILL' 1;"
                  >check_circle</span>
                  <span v-else class="w-3.5 h-3.5 rounded-full border border-slate-300 mt-1"></span>
                </button>
              </div>
            </div>

            <!-- Deskripsi Kronologi -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-sm font-semibold text-slate-800">
                  Deskripsi Kronologi Kejadian (<code class="text-xs font-mono text-slate-500">issues.description</code>)
                </label>
                <span class="text-xs text-slate-400">{{ issueDescription.length }} / 500 karakter</span>
              </div>
              <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 focus-within:bg-white focus-within:border-[#D81E5B] focus-within:ring-2 focus-within:ring-[#FCE7F3] transition-all">
                <textarea
                  v-model="issueDescription"
                  class="w-full bg-transparent text-sm text-slate-800 focus:outline-none resize-none leading-relaxed"
                  rows="4"
                  maxlength="500"
                ></textarea>
              </div>
              <p class="text-xs text-slate-500">Cantumkan lokasi fisik, identitas saksi di tempat (jika ada), serta penanda segel unik.</p>
            </div>

            <!-- Bukti Foto Lapangan -->
            <div class="space-y-2.5">
              <div class="flex items-center justify-between">
                <label class="text-sm font-semibold text-slate-800">Bukti Foto Lapangan &amp; Tag Lokasi Otomatis</label>
                <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                  <span class="material-symbols-outlined text-[15px]">verified</span>
                  <span>GPS Terverifikasi</span>
                </span>
              </div>

              <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex items-center gap-4">
                <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 relative bg-slate-200">
                  <img
                    class="w-full h-full object-cover"
                    alt="Foto segel rusak"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuAM4NnuboCZQugl3-VrgxXMTnwcFuSPgOtFJfW6y91Xo8OrKEjb82RY9gvgMmQaKLRKzqp2zSbvVYc_2neOjw35Pe60KO5vzd3fRBMC_7gY53Rf9_Mh0cAaGby_k-bBnlMcEg-aXuc17qFJCIaf8KpYWGarko7jzSGtzfe6pH8Tnz3WRBKtAEdlyQEcvjMAWnfPl63CCBIuNYL7zV9LU4c4QC64fsqw52GBOwIu9j3azj8mm8FjHYtWLw"
                  />
                  <div class="absolute bottom-1 right-1 bg-black/70 rounded px-1 text-[9px] text-white font-mono">JPG</div>
                </div>
                <div class="flex-1 min-w-0 space-y-1">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-800 font-semibold truncate">foto_segel_rusak_8891.jpg</span>
                    <span class="material-symbols-outlined text-emerald-600 text-[18px]">check_circle</span>
                  </div>
                  <p class="text-xs text-slate-500 truncate">Watermark GPS Tol Cawang / Salemba, 14 Okt 2026 15:15 WIB</p>
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-600 font-mono text-[11px]">3.4 MB</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 font-semibold text-[11px]">Exif Metadata OK</span>
                  </div>
                </div>
              </div>

              <div class="rounded-xl border border-dashed border-slate-300 hover:border-[#D81E5B] bg-slate-50 hover:bg-pink-50/40 p-3 flex items-center justify-center gap-2 cursor-pointer text-slate-600 transition-colors">
                <span class="material-symbols-outlined text-[18px] text-[#D81E5B]">add_a_photo</span>
                <span class="text-xs font-semibold">Ambil / Tambah Foto Tambahan (Maks. 3)</span>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="button"
              @click="handleIssueSubmitDirect"
              class="w-full py-3.5 px-6 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-sm transition-all flex items-center justify-center gap-2 mt-2 cursor-pointer active:scale-[0.99]"
            >
              <span class="material-symbols-outlined text-[20px]">lock_clock</span>
              <span>Kirim Laporan Kendala &amp; Tahan Pengiriman (status → HELD)</span>
            </button>
          </div>

          <!-- RIGHT COLUMN: Keputusan Resmi Admin QA & Eksekusi Retur -->
          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col gap-5">
            <!-- Live Status Banner -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-red-600 animate-ping shrink-0"></span>
                <div>
                  <div class="text-sm font-bold text-red-900 uppercase tracking-wide">STATUS SHIPMENT: HELD</div>
                  <p class="text-xs text-red-700">Ditahan untuk Pemeriksaan QA • AWB: {{ task.awb }}</p>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-full bg-red-600 text-white text-xs font-bold tracking-wider uppercase shrink-0">
                Akses Dikunci
              </span>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex items-start gap-3">
              <span class="material-symbols-outlined text-amber-600 text-[18px] shrink-0 mt-0.5">hourglass_top</span>
              <p class="text-xs text-slate-600 leading-relaxed">
                Menunggu instruksi lanjutan dari Dispatcher &amp; QA Lead Anteraja Pharma. Sesuai CDOB, dilarang membuka muatan atau meninggalkan titik pengantaran tanpa verifikasi.
              </p>
            </div>

            <!-- QA Admin Decision Card -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
              <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[#D81E5B]">verified_user</span>
                  <div>
                    <h3 class="text-sm font-bold text-slate-900">Keputusan Resmi Admin QA Control Tower</h3>
                    <p class="text-[11px] text-slate-500 font-mono">Mapped to: decisions table</p>
                  </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-slate-800 text-xs font-bold">DEC-2026-0941</span>
              </div>

              <!-- Decision Action Banner -->
              <div class="space-y-1.5">
                <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Aksi Putusan (<code class="font-mono">decisions.action</code>):</span>
                <div class="p-3 rounded-xl bg-red-600 text-white font-bold text-sm tracking-wide flex items-center gap-2 shadow-sm">
                  <span class="material-symbols-outlined text-[20px]">assignment_return</span>
                  <span>RETURN (Instruksi: Retur Kargo ke Gudang Asal)</span>
                </div>
              </div>

              <!-- Decision Reason -->
              <div class="space-y-1.5">
                <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Alasan Keputusan (<code class="font-mono">decisions.reason</code>):</span>
                <div class="bg-white border border-slate-200 p-3.5 rounded-xl text-xs text-slate-700 leading-relaxed">
                  “Segel fisik terbukti tidak utuh dan berpotensi merusak sterilitas biologis sesuai regulasi BPOM CDOB. <span class="font-bold text-red-600">JANGAN serahkan kargo ke pihak rumah sakit.</span> Segera kunci ulang cooler box dan bawa kembali kargo ke Hub Cawang untuk investigasi karantina.”
                </div>
              </div>

              <!-- Authorizer info -->
              <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200">
                <div class="flex items-center gap-2.5">
                  <img
                    class="w-9 h-9 rounded-full object-cover"
                    alt="dr. Ardiansyah, Apt."
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuDEIlLZxb-hUUU4MDB1meeVROOug4xALin76GqHpRqBMrJCcsK6a9IRba37BLGbGlp29pQpJQSHTR5ll5YgLqwjkQZVXQxpDrhRPDTOQr4XPiyNw71yw3UOtUSqLOb8sEzIvkOLvxA_laWVU5Hx7hIPdfrCCSBF0wZf1JioCxB-JHG7PZcDH7NZUKNRP7rU94xthyKvDckl8q0s9abJ21kDC6q8qowtVW8vDmwzGpmy6wWZTILNsOxRJA"
                  />
                  <div>
                    <div class="text-xs font-bold text-slate-900">dr. Ardiansyah, Apt.</div>
                    <div class="text-[11px] text-slate-500">QA Lead &amp; Penanggung Jawab Teknis CDOB</div>
                  </div>
                </div>
                <div class="text-left sm:text-right font-mono text-[11px]">
                  <div class="text-slate-800 font-semibold">14 Okt 2026, 15:22 WIB</div>
                  <div class="text-emerald-600 flex items-center sm:justify-end gap-1">
                    <span class="material-symbols-outlined text-[13px]">lock</span>
                    <span>RSA-2048 Signed</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Telemetry snapshot -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-pink-100 flex items-center justify-center text-[#D81E5B] shrink-0">
                  <span class="material-symbols-outlined text-[20px]">sensors</span>
                </div>
                <div>
                  <div class="text-xs font-bold text-slate-800">Sensor S-CLD-991 Aktif</div>
                  <div class="text-xs text-slate-500">Suhu Kargo: <span class="font-mono font-bold text-emerald-600">3.4°C</span> (Aman dalam rentang 2–8°C)</div>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold shrink-0">Logger ON</span>
            </div>

            <!-- Action Button -->
            <div class="space-y-2 mt-auto pt-2">
              <button
                type="button"
                @click="handleConfirmReturn"
                :disabled="isReturning"
                class="w-full py-3.5 px-6 rounded-xl bg-[#D81E5B] hover:bg-[#C2185B] text-white text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-80 disabled:cursor-not-allowed active:scale-[0.99]"
              >
                <span class="material-symbols-outlined text-[20px]">sync_alt</span>
                <span v-if="isReturning">✓ Instruksi Retur Dikonfirmasi (returning)</span>
                <span v-else>Konfirmasi &amp; Jalankan Instruksi Retur (Update shipments → returning)</span>
              </button>
              <p class="text-center text-xs text-slate-500">
                Sistem akan memperbarui rute navigasi kembali ke Gudang Cawang dan otomatis menerbitkan e-Berita Acara Retur Karantina.
              </p>
            </div>
          </div>
        </div>

        <!-- Audit Log Kejadian Cold-Chain underneath -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-slate-500 text-[18px]">history</span>
              <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Audit Log Kejadian Cold-Chain</span>
            </div>
            <span class="text-xs text-slate-500 font-mono">ISO 13485:2016 Compliant</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-start gap-2.5">
              <span class="material-symbols-outlined text-emerald-600 text-[16px] shrink-0 mt-0.5">check_circle</span>
              <div>
                <div class="text-xs font-semibold text-slate-800">15:15 WIB • Issue Logged</div>
                <div class="text-xs text-slate-500">Kurir melaporkan anomali segel fisik SL-8891.</div>
              </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-start gap-2.5">
              <span class="material-symbols-outlined text-emerald-600 text-[16px] shrink-0 mt-0.5">check_circle</span>
              <div>
                <div class="text-xs font-semibold text-slate-800">15:16 WIB • Status Changed: HELD</div>
                <div class="text-xs text-slate-500">Otomatisasi ERP Anteraja mengunci AWB {{ task.awb }}.</div>
              </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-start gap-2.5">
              <span class="material-symbols-outlined text-[#D81E5B] text-[16px] shrink-0 mt-0.5">mark_chat_read</span>
              <div>
                <div class="text-xs font-semibold text-slate-800">15:22 WIB • QA Decision Authorized</div>
                <div class="text-xs text-slate-500">Instruksi RETUR disahkan oleh dr. Ardiansyah.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: SERAH TERIMA & LAPORAN -->
      <div v-else-if="activeTab === 4" class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">
        <!-- Header Title & Subtitle -->
        <div class="space-y-2">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold tracking-wide uppercase">
            <span>✦ SERAH TERIMA KLINIS BERSTANDAR CDOB</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
            4. Serah Terima &amp; Laporan BAP
          </h1>
          <p class="text-sm text-slate-600 max-w-3xl">
            Verifikasi identitas apoteker penanggung jawab, bukti foto, e-POD, dan unduhan salinan BAP.
          </p>
        </div>

        <!-- 2-Column Grid Anatomy -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
          <!-- LEFT COLUMN: Form Bukti Serah Terima (e-POD) -->
          <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-xs text-[#D81E5B] uppercase font-bold tracking-wider">Kepatuhan Regulasi BPOM</div>
                <h2 class="text-lg font-bold text-slate-900 mt-0.5">Form Bukti Serah Terima (e-POD) &amp; Validasi CDOB</h2>
              </div>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                <span>CDOB Terverifikasi</span>
              </span>
            </div>

            <!-- Toggle Mode: Serah Terima Normal vs Retur Gudang -->
            <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl">
              <button
                type="button"
                id="tab-normal"
                @click="handoverMode = 'normal'"
                class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="handoverMode === 'normal'
                  ? 'bg-white text-[#D81E5B] shadow-sm'
                  : 'text-slate-600 hover:text-slate-900'"
              >
                <span class="material-symbols-outlined text-base font-bold">check_circle</span>
                <span>Serah Terima Normal</span>
              </button>
              <button
                type="button"
                id="tab-return"
                @click="handoverMode = 'return'"
                class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-medium transition-all cursor-pointer"
                :class="handoverMode === 'return'
                  ? 'bg-white text-[#D81E5B] font-bold shadow-sm'
                  : 'text-slate-600 hover:text-slate-900'"
              >
                <span class="material-symbols-outlined text-base">assignment_return</span>
                <span>Retur Gudang</span>
              </button>
            </div>

            <div class="space-y-4">
              <!-- APJ Card -->
              <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl flex items-start justify-between gap-4">
                <div class="space-y-1">
                  <span class="text-xs text-slate-500 uppercase font-semibold">Petugas Penerima Farmasi Resmi (APJ)</span>
                  <div class="text-base font-bold text-slate-900">Apt. Siti Rahma, S.Farm</div>
                  <div class="text-xs text-slate-600 flex items-center gap-2">
                    <span>{{ task.destination_name || 'RS Medika Sehat Jakarta' }}</span>
                    <span>•</span>
                    <span class="font-mono text-xs font-medium">NIPA: 19890412/APJ-RSMS</span>
                  </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-xl">medical_services</span>
                </div>
              </div>

              <!-- 2 Stat Boxes -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl space-y-1.5">
                  <span class="text-xs text-slate-500 uppercase font-semibold">Suhu Fisik Serah Terima</span>
                  <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-emerald-700">4.4°C</span>
                    <span class="text-xs text-slate-500 font-medium">IoT #TH-902</span>
                  </div>
                  <div class="inline-flex items-center gap-1 text-[11px] text-emerald-800 bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">
                    <span class="material-symbols-outlined text-xs">thermostat</span>
                    <span>Lolos CPOB (2.0° - 8.0°C)</span>
                  </div>
                </div>

                <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl space-y-1.5">
                  <span class="text-xs text-slate-500 uppercase font-semibold">Kondisi Segel Boks</span>
                  <div class="text-base font-bold text-slate-900 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-emerald-600 text-lg">lock</span>
                    <span>{{ task.seal_str ? task.seal_str.split(' ')[0] : 'SL-8891' }}</span>
                  </div>
                  <div class="inline-flex items-center gap-1 text-[11px] text-emerald-800 bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">
                    <span class="material-symbols-outlined text-xs">verified_user</span>
                    <span>Utuh &amp; Sesuai Manifest</span>
                  </div>
                </div>
              </div>

              <!-- Documentation Photos -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-slate-500 uppercase font-semibold">Dokumentasi Foto Serah Terima Fisik</span>
                  <span class="text-xs text-emerald-700 font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm font-bold">location_on</span>
                    <span>Geo-Tagged Valid</span>
                  </span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="relative group rounded-xl overflow-hidden bg-slate-100 aspect-video">
                    <img
                      class="w-full h-full object-cover"
                      alt="Boks Vaksin #BX-01"
                      src="https://lh3.googleusercontent.com/aida-public/AB6AXuC6I3ZdAyE_fXfAVRm4MYTHaCZ1DY8w9hGvtnQClFq-HGUFxaf0TibWgutUXTVe_4CxHcimC7Lb-8W2-64xE4S5CoSUp-pwVCEgCLsTaTpP-XrcA36rtNW-hem1bMCFwj_ivmjH8ZmAcVmQp5g-WnPMhOAf3CzQ3mqm81aniQmC3kN6RHUDSDwphUsui1aCK3M0UWoJfdMzxhj5sIDA2fMzpyyYdcO1kooh4aGZVrsqVq8GwTf51DxXeg"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent flex flex-col justify-end p-2.5 text-white">
                      <span class="text-xs font-bold">Boks Vaksin #BX-01</span>
                      <span class="text-[10px] text-slate-200">14/10/2026 15:16:22 WIB</span>
                    </div>
                  </div>

                  <div class="relative group rounded-xl overflow-hidden bg-slate-100 aspect-video">
                    <img
                      class="w-full h-full object-cover"
                      alt="Inspeksi Segel SL-8891"
                      src="https://lh3.googleusercontent.com/aida-public/AB6AXuDTOgwSQjoJLqKgis2rLUvej38QhxlL9rG2OwNVYWs2WdcJklUugG84V879p89hCOFJX7lj5iTRzoynk0PdjLnHMr1un8Dbb6oVgcIcEGrXtqU9Si-e3bJ1yDLvgHitT2uEwn80HBITXg0rj5TZaMd7U9jaEp0y_HWZk9ig26sDCckdqC9FId-WEUGeYBceppuIhKA60Cn1roFtcSn7BaEEYmGS9Wg_pFHGwPgcmebesITLwBO8M5Kvyw"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent flex flex-col justify-end p-2.5 text-white">
                      <span class="text-xs font-bold">Inspeksi Segel SL-8891</span>
                      <span class="text-[10px] text-slate-200">14/10/2026 15:17:05 WIB</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Digital Signature -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-slate-500 uppercase font-semibold">Tanda Tangan Digital Apoteker (NIPA Disahkan)</span>
                  <span class="font-mono text-xs text-slate-500">SHA256: 8f4a...91b2</span>
                </div>
                <div class="relative bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center min-h-[110px] overflow-hidden">
                  <svg class="w-48 h-16 text-slate-800 opacity-80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 200 60">
                    <path d="M15 45 C 30 10, 40 50, 55 25 C 65 10, 75 35, 90 20 C 105 10, 110 40, 130 15 C 145 0, 155 35, 175 25 C 185 20, 190 28, 195 22"></path>
                    <path d="M40 50 L 170 48"></path>
                  </svg>
                  <div class="absolute bottom-2 right-3 flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 px-2.5 py-0.5 rounded shadow-sm">
                    <span class="material-symbols-outlined text-xs font-bold">verified</span>
                    <span>Tervalidasi Digital Token</span>
                  </div>
                </div>
              </div>
            </div>

            <button
              type="button"
              class="w-full py-3.5 px-4 rounded-xl bg-slate-100 text-slate-500 font-bold text-sm flex items-center justify-center gap-2 cursor-not-allowed border border-slate-200"
              disabled
            >
              <span class="material-symbols-outlined text-emerald-600 text-xl font-bold">task_alt</span>
              <span>✓ Tugas Pengiriman Selesai &amp; Ditutup (15:18 WIB)</span>
            </button>
          </div>

          <!-- RIGHT COLUMN: Ringkasan Kargo, Riwayat Kronologis & BAP -->
          <div class="space-y-6">
            <!-- Ringkasan Kargo Cold-Chain -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500 uppercase font-semibold">Ringkasan Kargo Cold-Chain</span>
                <span class="px-2.5 py-0.5 rounded-full bg-[#FCE7F3] text-[#D81E5B] text-xs font-bold">
                  {{ task.weight_str ? (task.weight_str.split('•')[0] || '2 Koli').trim() : '2 Koli' }} Terverifikasi
                </span>
              </div>
              <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-[#FCE7F3] text-[#D81E5B] flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-2xl">vaccines</span>
                </div>
                <div class="min-w-0 flex-1">
                  <h3 class="text-base font-bold text-slate-900 truncate">
                    {{ task.item_title || 'Vaksin Meningococcal Konjugat ACYW' }}
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">
                    Batch: <span class="font-mono font-semibold text-slate-800">{{ task.batch_number || '#MCV-2026-X12' }}</span> • Exp: Nov 2027
                  </p>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-xs text-slate-500">Asal Titik Muat</span>
                  <p class="text-xs sm:text-sm text-slate-800 font-bold truncate mt-0.5">
                    {{ task.origin_name || 'Hub Pharma Cawang (DC)' }}
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-xs text-slate-500">Fasilitas Tujuan</span>
                  <p class="text-xs sm:text-sm text-slate-800 font-bold truncate mt-0.5">
                    {{ task.destination_name || 'RS Medika Sehat Jakarta' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Riwayat Kronologis (Chain of Custody) -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-xs text-[#D81E5B] uppercase font-bold tracking-wider">Log Audit Peristiwa</span>
                  <h3 class="text-base font-bold text-slate-900 mt-0.5">Riwayat Kronologis (Chain of Custody)</h3>
                </div>
                <span class="text-xs font-mono bg-slate-100 px-2.5 py-1 rounded-md text-slate-600 font-semibold">4 Events</span>
              </div>
              <div class="space-y-3 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-slate-200">
                <!-- Event 1 -->
                <div class="relative flex items-start gap-4">
                  <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 ring-4 ring-white z-10">
                    <span class="material-symbols-outlined text-sm font-bold">check</span>
                  </div>
                  <div class="flex-1 min-w-0 bg-slate-50 border border-slate-100 p-3 rounded-xl">
                    <div class="flex items-center justify-between gap-2">
                      <span class="text-xs font-bold text-emerald-700 uppercase">15:18 WIB • DELIVER</span>
                      <span class="font-mono text-xs text-emerald-700 font-bold">4.4°C</span>
                    </div>
                    <p class="text-xs text-slate-700 mt-1">
                      Kargo diserahkan sah ke APJ Apt. Siti Rahma di Farmasi RS Medika Sehat.
                    </p>
                  </div>
                </div>

                <!-- Event 2 -->
                <div class="relative flex items-start gap-4">
                  <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 ring-4 ring-white z-10">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                  </div>
                  <div class="flex-1 min-w-0 bg-slate-50 border border-slate-100 p-3 rounded-xl">
                    <div class="flex items-center justify-between gap-2">
                      <span class="text-xs font-bold text-slate-600 uppercase">15:05 WIB • ARRIVE</span>
                      <span class="font-mono text-xs text-slate-600 font-semibold">4.5°C</span>
                    </div>
                    <p class="text-xs text-slate-700 mt-1">
                      Tiba di area drop-off Instalasi Farmasi RS Medika Sehat.
                    </p>
                  </div>
                </div>

                <!-- Event 3 -->
                <div class="relative flex items-start gap-4">
                  <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 ring-4 ring-white z-10">
                    <span class="material-symbols-outlined text-sm">local_shipping</span>
                  </div>
                  <div class="flex-1 min-w-0 bg-slate-50 border border-slate-100 p-3 rounded-xl">
                    <div class="flex items-center justify-between gap-2">
                      <span class="text-xs font-bold text-slate-600 uppercase">13:45 WIB • DEPART</span>
                      <span class="font-mono text-xs text-slate-600 font-semibold">4.3°C</span>
                    </div>
                    <p class="text-xs text-slate-700 mt-1">
                      Berangkat dari Hub Cawang via Tol Dalam Kota. Kotak pendingin aktif.
                    </p>
                  </div>
                </div>

                <!-- Event 4 -->
                <div class="relative flex items-start gap-4">
                  <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 ring-4 ring-white z-10">
                    <span class="material-symbols-outlined text-sm">assignment_turned_in</span>
                  </div>
                  <div class="flex-1 min-w-0 bg-slate-50 border border-slate-100 p-3 rounded-xl">
                    <div class="flex items-center justify-between gap-2">
                      <span class="text-xs font-bold text-slate-600 uppercase">12:45 WIB • ACCEPT</span>
                      <span class="font-mono text-xs text-slate-600 font-semibold">4.2°C</span>
                    </div>
                    <p class="text-xs text-slate-700 mt-1">
                      Kurir Bambang menerima penugasan &amp; validasi pra-pendinginan.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- BAP Report Document Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-[#D81E5B] border border-rose-100 flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-2xl">picture_as_pdf</span>
                </div>
                <div class="min-w-0 flex-1">
                  <span class="text-xs text-[#D81E5B] uppercase font-bold">Dokumen Resmi Terbit</span>
                  <h3 class="text-base font-bold text-slate-900 leading-snug mt-0.5">
                    Berita Acara Penerimaan (BAP) &amp; Laporan Audit Rantai Dingin
                  </h3>
                  <p class="font-mono text-xs text-slate-500 mt-1">ID: BAP-PHA-20261014-8891 • PDF CDOB BPOM Validated</p>
                </div>
              </div>

              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span class="flex items-center gap-1.5 font-medium">
                  <span class="material-symbols-outlined text-base text-emerald-600">security</span>
                  <span>Enkripsi Digital SHA-256 Otentik</span>
                </span>
                <span class="font-mono font-semibold">1.8 MB</span>
              </div>

              <div class="space-y-3 pt-1">
                <button
                  type="button"
                  @click="handleDownloadBapPdf"
                  class="w-full bg-[#D81E5B] hover:bg-[#C2185B] text-white font-bold text-sm py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 shadow-sm transition-colors cursor-pointer active:scale-[0.99]"
                >
                  <span class="material-symbols-outlined text-xl">download</span>
                  <span>Unduh Salinan Laporan Tugas Kurir (PDF)</span>
                </button>
                <button
                  type="button"
                  @click="handleShareWhatsappDispatcher"
                  class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors border border-slate-200 cursor-pointer active:scale-[0.99]"
                >
                  <span class="material-symbols-outlined text-emerald-600 text-lg">chat</span>
                  <span>Kirim Ringkasan Laporan ke WhatsApp Dispatcher</span>
                </button>
              </div>
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

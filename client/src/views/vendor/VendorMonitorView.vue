<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Shipment } from '../../types/shipment'
import { mockShipments } from '../../data/mockShipments'
import MonitorTabNav from '../../components/monitor/MonitorTabNav.vue'
import TelemetrySubHeader from '../../components/telemetry/TelemetrySubHeader.vue'
import LiveLocationRibbon from '../../components/telemetry/LiveLocationRibbon.vue'
import LiveGpsMapCard from '../../components/telemetry/LiveGpsMapCard.vue'
import CourierCard from '../../components/telemetry/CourierCard.vue'
import TemperatureTelemetryChart from '../../components/telemetry/TemperatureTelemetryChart.vue'
import FieldIssueCard from '../../components/telemetry/FieldIssueCard.vue'
import DeliveryHandoverFooter from '../../components/telemetry/DeliveryHandoverFooter.vue'
import AuditSubHeaderBanner from '../../components/audit/AuditSubHeaderBanner.vue'
import AuditTrailTimeline from '../../components/audit/AuditTrailTimeline.vue'
import BapCertificateCard from '../../components/audit/BapCertificateCard.vue'
import PackagingAssuranceCard from '../../components/audit/PackagingAssuranceCard.vue'
import { mockAuditEvents, mockBapReport } from '../../data/mockShipments'
import NotificationToast from '../../components/vendor/NotificationToast.vue'

const route = useRoute()
const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)

const awb = computed(() => (route.params.awb as string) || '#100098421088712')
const activeTab = ref<number>(Number(route.query.screen) || 1)

// Watch query param changes to keep activeTab in sync
watch(
  () => route.query.screen,
  (newScreen) => {
    activeTab.value = Number(newScreen) || 1
  }
)

// Find matching shipment or fallback
const shipment = computed<Shipment>(() => {
  const decodedAwb = decodeURIComponent(awb.value).trim().toLowerCase()
  const cleanSearch = decodedAwb.replace(/^#/, '').replace(/^awb\s*/, '')

  return (
    mockShipments.find(s => {
      const sTracking = s.tracking_number.toLowerCase().replace(/^#/, '')
      return sTracking === cleanSearch || s.tracking_number.toLowerCase() === decodedAwb
    }) ||
    mockShipments.find(s => s.tracking_number === '#100098421088712') ||
    mockShipments[0]!
  )
})

// Search Bar State
const searchAwbInput = ref<string>(`AWB ${shipment.value.tracking_number}`)

// Keep input updated when shipment changes
watch(
  () => shipment.value.tracking_number,
  (newTracking) => {
    searchAwbInput.value = `AWB ${newTracking}`
  }
)

// WhatsApp notification toggle
const waNotificationEnabled = ref<boolean>(true)

function handleTabChange(tab: number) {
  activeTab.value = tab
  router.replace({
    query: { ...route.query, screen: tab.toString() },
  })

  const tabNames: Record<number, string> = {
    1: 'Halaman Pantau Pengiriman',
    2: 'Pelacakan GPS & Telemetri Suhu',
    3: 'Laporan Audit & BAP Digital',
  }
  const tabTitle = tabNames[tab] || 'Halaman Pantau'
  toastRef.value?.show(tabTitle, `Memuat data navigasi untuk resi ${shipment.value.tracking_number}...`)
}

function handleSearchAwb() {
  const query = searchAwbInput.value.trim().replace(/^awb\s*/i, '').replace(/^#/, '')
  if (!query) return

  const found = mockShipments.find(s => {
    const sClean = s.tracking_number.toLowerCase().replace(/^#/, '')
    return sClean.includes(query.toLowerCase()) || s.item_summary.toLowerCase().includes(query.toLowerCase())
  })

  if (found) {
    toastRef.value?.show(
      'Kargo Ditemukan!',
      `Memuat data pemantauan untuk ${found.tracking_number} (${found.item_summary}).`
    )
    router.push({
      path: `/vendor/monitor/${encodeURIComponent(found.tracking_number)}`,
      query: { ...route.query, screen: '1' },
    })
  } else {
    toastRef.value?.show(
      'Melacak Kargo',
      `Menghubungkan ke telemetri sensor kargo #${query}...`
    )
    router.push({
      path: `/vendor/monitor/${encodeURIComponent(query)}`,
      query: { ...route.query, screen: '1' },
    })
  }
}

function selectRiwayat(resi: string) {
  searchAwbInput.value = `AWB ${resi}`
  handleSearchAwb()
}

function handleToggleWa() {
  if (waNotificationEnabled.value) {
    toastRef.value?.show(
      'Notifikasi WhatsApp Aktif',
      'Tautan pelacakan GPS langsung akan dikirimkan otomatis ke WhatsApp Apoteker saat armada diberangkatkan.'
    )
  } else {
    toastRef.value?.show(
      'Notifikasi WhatsApp Dimatikan',
      'Pemberitahuan otomatis via WhatsApp dinonaktifkan untuk pengiriman ini.'
    )
  }
}

function handleCopyAwb() {
  toastRef.value?.show(
    'No. Resi Disalin!',
    `Nomor AWB ${shipment.value.tracking_number} berhasil disalin ke clipboard.`
  )
}

function handleDownloadLogPdf() {
  toastRef.value?.show(
    'Mengunduh Data Log Suhu (.PDF)',
    'Laporan PDF data suhu sedang diunduh sesuai standar CDOB BPOM.'
  )
}

function handleDownloadBap(filename: string) {
  toastRef.value?.show(
    'Berita Acara Terunduh',
    `Berkas ${filename} berhasil diunduh dan terverifikasi tanda tangan digital APJ.`
  )
}

function handleShareTelegram(chatId: string) {
  toastRef.value?.show(
    'Notifikasi Telegram Terkirim',
    `Salinan dokumen BAP dan ringkasan audit telah dikirimkan ke ${chatId}.`
  )
}

function handlePrintBap() {
  toastRef.value?.show(
    'Mencetak Dokumen BAP',
    'Menyiapkan salinan fisik untuk arsip kepatuhan BPOM.'
  )
}
</script>

<template>
  <div class="w-full flex flex-col space-y-6">
    <!-- ========================================================================= -->
    <!-- TAB 1: Halaman Pantau (Desain Baru Smart AI Rantai Dingin)                -->
    <!-- ========================================================================= -->
    <div v-if="activeTab === 1" class="space-y-6">
      <!-- 1. Hero Header & Search Bar -->
      <div class="bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm space-y-5">
        <div class="space-y-2">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
            <span>✦</span>
            <span>AI-POWERED PHARMA DIAGNOSTICS</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Lacak Pengiriman Smart AI
          </h1>
          <p class="text-slate-500 text-sm">
            Pemantauan rantai dingin real-time terintegrasi sensor suhu dan telemetri armada
          </p>
        </div>

        <!-- Search / AWB input bar -->
        <form @submit.prevent="handleSearchAwb" class="flex flex-col sm:flex-row items-center gap-3 pt-1">
          <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <span class="material-symbols-outlined text-[20px]">barcode_scanner</span>
            </div>
            <input
              v-model="searchAwbInput"
              type="text"
              class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#D81E5B]/20 focus:border-[#D81E5B]"
              placeholder="Masukkan nomor AWB / Resi kargo..."
            />
          </div>
          <button
            type="submit"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#C2185B] hover:bg-[#b10045] text-white rounded-xl px-6 py-3 font-semibold text-sm shadow-sm transition-all shrink-0 cursor-pointer"
          >
            <span class="material-symbols-outlined text-[18px]">search</span>
            <span>Lacak Paket</span>
          </button>
        </form>

        <!-- Recent search pills -->
        <div class="flex flex-wrap items-center gap-2 pt-1 text-xs text-slate-400">
          <span class="font-medium">Riwayat:</span>
          <button
            type="button"
            @click="selectRiwayat('PHA-260104')"
            class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-mono font-medium hover:bg-slate-200 cursor-pointer transition-colors"
          >
            #PHA-260104
          </button>
          <button
            type="button"
            @click="selectRiwayat('100098421088712')"
            class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-mono font-medium hover:bg-slate-200 cursor-pointer transition-colors"
          >
            #100098421088712
          </button>
          <button
            type="button"
            @click="selectRiwayat('VAX-MRA-8802')"
            class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-mono font-medium hover:bg-slate-200 cursor-pointer transition-colors"
          >
            #VAX-MRA-8802
          </button>
          <button
            type="button"
            @click="selectRiwayat('INS-JKT-1092')"
            class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-mono font-medium hover:bg-slate-200 cursor-pointer transition-colors"
          >
            #INS-JKT-1092
          </button>
        </div>
      </div>

      <!-- 2. Prominent Magenta Status Banner -->
      <div class="bg-gradient-to-r from-[#D81E5B] to-[#AD1457] text-white p-5 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0 border border-white/20 text-white">
            <span class="material-symbols-outlined text-[24px]">warehouse</span>
          </div>
          <div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-100">• STATUS PESANAN</p>
            <h2 class="text-base sm:text-lg font-bold text-white">
              PEMERIKSAAN GUDANG — Sedang Diperiksa Kualitas &amp; Suhu di {{ shipment.last_location || 'Hub Cawang' }}
            </h2>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start md:self-auto shrink-0">
          <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-500/90 text-white text-xs font-semibold backdrop-blur-sm border border-emerald-400/40">
            <span class="material-symbols-outlined text-[14px]">check_circle</span>
            Controlled {{ shipment.temperature_min.toFixed(1) }}°C – {{ shipment.temperature_max.toFixed(1) }}°C
          </span>
          <span class="bg-amber-400 text-slate-900 font-bold px-3 py-1 rounded-full text-xs shadow-sm">
            Menunggu Rilis QC
          </span>
          <span class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-medium border border-white/25">
            Est. Keberangkatan 10:15 WIB
          </span>
        </div>
      </div>

      <!-- 3. Hero Card: Status & Minimal Linear Stepper -->
      <section class="bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-8 border-b border-slate-100">
          <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-[#b10045] text-xs font-semibold mb-2">
              <span class="w-2 h-2 rounded-full bg-[#D81E5B] animate-pulse"></span>
              Pemeriksaan Pra-Kirim
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
              Menyiapkan Pengiriman Anda
            </h1>
            <p class="text-slate-500 text-sm mt-1">
              Pemeriksaan kualitas sedang berlangsung di Hub Jakarta Pusat
            </p>
          </div>
          <div class="flex items-center gap-2 self-start md:self-auto text-xs font-medium text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
            <span class="material-symbols-outlined text-slate-400 text-[18px]">schedule</span>
            <span>Est. Keberangkatan: <strong class="text-slate-800 font-semibold">10:15 WIB</strong></span>
          </div>
        </div>

        <!-- Minimal 5-Step Linear Stepper -->
        <div class="pt-8">
          <div class="relative">
            <!-- Continuous Track Line (Desktop) -->
            <div class="hidden sm:block absolute top-5 left-8 right-8 h-0.5 bg-slate-200 -z-0">
              <div class="h-full bg-emerald-500 w-1/4"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-6 sm:gap-2 relative z-10">
              <!-- Step 1: Done -->
              <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-200">
                  <span class="material-symbols-outlined text-[20px]">check</span>
                </div>
                <div class="text-left sm:text-center">
                  <p class="text-xs font-bold text-slate-900">Pesanan Dikonfirmasi</p>
                  <p class="text-[11px] text-emerald-600 font-semibold">08:15 WIB • Selesai</p>
                </div>
              </div>

              <!-- Step 2: Active -->
              <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
                <div class="w-10 h-10 rounded-full bg-[#D81E5B] text-white flex items-center justify-center shrink-0 shadow-sm shadow-rose-300 ring-4 ring-rose-100">
                  <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                </div>
                <div class="text-left sm:text-center">
                  <p class="text-xs font-bold text-[#b10045]">Inspeksi QC Gudang</p>
                  <p class="text-[11px] text-[#D81E5B] font-semibold">Sedang Diproses</p>
                </div>
              </div>

              <!-- Step 3: Upcoming -->
              <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 opacity-60">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                  <span class="text-xs font-bold">3</span>
                </div>
                <div class="text-left sm:text-center">
                  <p class="text-xs font-medium text-slate-600">Siap Diberangkatkan</p>
                  <p class="text-[11px] text-slate-400">Est. 10:15 WIB</p>
                </div>
              </div>

              <!-- Step 4: Upcoming -->
              <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 opacity-60">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                  <span class="text-xs font-bold">4</span>
                </div>
                <div class="text-left sm:text-center">
                  <p class="text-xs font-medium text-slate-600">Dalam Perjalanan</p>
                  <p class="text-[11px] text-slate-400">Truk Pendingin</p>
                </div>
              </div>

              <!-- Step 5: Upcoming -->
              <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2 opacity-60">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0">
                  <span class="text-xs font-bold">5</span>
                </div>
                <div class="text-left sm:text-center">
                  <p class="text-xs font-medium text-slate-600">Tiba di Tujuan</p>
                  <p class="text-[11px] text-slate-400">Instalasi Farmasi</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. Essential Overview Card (Spacious 2-Column Minimalist View) -->
      <section class="bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
          <!-- Left Column: Consignment & Route -->
          <div class="space-y-6">
            <div class="flex items-center justify-between pb-2">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">RINCIAN PENGIRIMAN</span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-semibold">
                <span class="material-symbols-outlined text-[15px]">ac_unit</span>
                Wajib Suhu Ketat {{ shipment.temperature_min.toFixed(1) }}°C – {{ shipment.temperature_max.toFixed(1) }}°C
              </span>
            </div>

            <!-- Medicine Name & Packaging -->
            <div>
              <p class="text-xs font-medium text-slate-500">Kargo Farmasi</p>
              <h2 class="text-xl font-bold text-slate-900 mt-0.5">
                {{ shipment.item_summary || '50 Vial – Insulin Reguler & Biologis' }}
              </h2>
              <p class="text-xs text-slate-500 mt-1">
                Batch Tunggal: <span class="font-mono font-medium text-slate-700">{{ shipment.batch_number || '#VAX - MRA - 8802' }}</span> • {{ shipment.package_type || 'Boks Pendingin Terinsulasi (VIP-4)' }}
              </p>
            </div>

            <!-- Route Locations -->
            <div class="space-y-4 pt-2">
              <!-- Origin -->
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 mt-0.5">
                  <span class="material-symbols-outlined text-[18px]">store</span>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">HUB ASAL</p>
                  <p class="text-sm font-semibold text-slate-800">{{ shipment.sender_name || 'Central Cold Storage Cikarang' }}</p>
                  <p class="text-xs text-slate-500">PT Biofarma Global Nusantara • Dok 4</p>
                </div>
              </div>

              <!-- Route Line Indicator -->
              <div class="pl-4 -my-2">
                <div class="w-0.5 h-4 bg-slate-200"></div>
              </div>

              <!-- Destination -->
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-[#b10045] flex items-center justify-center shrink-0 mt-0.5">
                  <span class="material-symbols-outlined text-[18px]">local_hospital</span>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-[#b10045]">TUJUAN PENGANTARAN</p>
                  <p class="text-sm font-semibold text-slate-800">{{ shipment.recipient_name || 'Instalasi Farmasi RS Medika Sehat, Gd. B' }}</p>
                  <p class="text-xs text-slate-500">{{ shipment.address || 'Jl. Gatot Subroto Kav. 42, Jakarta Selatan' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Verification & Live Core Temperature -->
          <div class="pt-6 lg:pt-0 lg:pl-12 space-y-6">
            <div class="flex items-center justify-between pb-2">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">STATUS KUALITAS &amp; SUHU</span>
              <!-- Seal Verified Badge -->
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">
                <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
                Segel Terverifikasi &amp; Lolos
              </span>
            </div>

            <!-- Large Clear Temperature Readout -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <p class="text-xs font-medium text-slate-500">Suhu Inti Awal</p>
                <div class="flex items-baseline gap-1 mt-1">
                  <span class="text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
                    {{ (shipment.current_temperature ?? 4.2).toFixed(1) }}
                  </span>
                  <span class="text-2xl font-bold text-slate-500">°C</span>
                </div>
              </div>
              <div class="flex flex-col items-start sm:items-end gap-1.5">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-500 text-white text-xs font-bold shadow-sm shadow-emerald-200">
                  <span class="material-symbols-outlined text-[14px]">check</span>
                  Rentang Normal (2°C – 8°C)
                </span>
                <span class="text-[11px] text-slate-400">Stabilisasi PCM pasif • 0 deviasi suhu</span>
              </div>
            </div>

            <!-- Certified Checker PIC -->
            <div class="p-4 rounded-xl border border-slate-100 flex items-center justify-between gap-3 bg-white">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 font-bold text-sm">
                  HW
                </div>
                <div>
                  <p class="text-xs text-slate-400 font-medium">Diperiksa &amp; Dikalibrasi oleh</p>
                  <p class="text-sm font-bold text-slate-800">
                    {{ shipment.inspection?.checker_name || 'Hendra Wijaya, S.Farm' }}
                    <span class="text-xs font-normal text-slate-500">(QC Bersertifikasi)</span>
                  </p>
                </div>
              </div>
              <span class="material-symbols-outlined text-emerald-600 text-[20px]" title="Bersertifikasi CDOB">verified</span>
            </div>
          </div>
        </div>
      </section>

      <!-- 5. Action Notification Card (WhatsApp Departure Toggle) -->
      <div class="p-5 rounded-2xl bg-white border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">chat</span>
          </div>
          <div>
            <p class="text-sm font-bold text-slate-900">Terima Notifikasi WhatsApp saat Armada Berangkat</p>
            <p class="text-xs text-slate-500">Tautan pelacakan GPS langsung akan dikirim ke apoteker penerima rumah sakit</p>
          </div>
        </div>

        <!-- Toggle Switch -->
        <label class="relative inline-flex items-center cursor-pointer shrink-0 self-end sm:self-auto">
          <input
            id="wa-departure-toggle"
            type="checkbox"
            class="sr-only peer"
            v-model="waNotificationEnabled"
            @change="handleToggleWa"
          />
          <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
        </label>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: Pelacakan GPS & Telemetri Suhu Live                                -->
    <!-- ========================================================================= -->
    <div v-else-if="activeTab === 2" class="flex flex-col w-full space-y-6">
      <TelemetrySubHeader
        :shipment="shipment"
        @copy-awb="handleCopyAwb"
      />

      <!-- Live Location Ribbon -->
      <LiveLocationRibbon
        :location-text="shipment.last_location || 'Tol Dalam Kota - Cawang (Menuju RS Medika Sehat, Kuningan)'"
        updated-ago="Baru saja"
      />

      <!-- 2-Column Responsive Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Live Map & Courier Assignment -->
        <div class="lg:col-span-2 flex flex-col gap-6">
          <LiveGpsMapCard
            :origin-name="shipment.sender_name"
            origin-time="Pukul 11:20 WIB"
            last-gps-point="KM 02 Tol Cawang"
            last-gps-time="Pukul 15:06 WIB"
            :destination-name="shipment.recipient_name"
            destination-eta="Est. 15:30 WIB"
            remaining-distance="8.4 KM"
            reefer-plate="Reefer Satria B 9482 PBF"
            average-temp="Stabil 4.5°C"
          />

          <CourierCard
            courier-name="Bambang Suryono"
            courier-id="STR-PHR-094"
            fleet-plate="Reefer Dedicated Box B 9482 PBF"
            status-text="Satria siap mengantar / Aktif di rute"
            reefer-range="2.0°C – 8.0°C Aktif"
            scheduled-at="14 Okt 2026, 15:30 WIB"
            :phone-url="`https://wa.me/6281234567890?text=Halo%20Satria%20Bambang,%20konfirmasi%20kiriman%20${encodeURIComponent(shipment.item_summary)}%20RS%20Medika%20Sehat%20AWB%20${shipment.tracking_number}`"
          />
        </div>

        <!-- Right Column: Temperature Telemetry & Field Issue Monitor -->
        <div class="flex flex-col gap-6">
          <TemperatureTelemetryChart
            sensor-id="SNS-CO-8821"
            :current-temp="shipment.current_temperature || 4.5"
            :target-temp="4.0"
            :battery-pct="94"
            calibration-expiry="Aktif s/d 2027"
          />

          <FieldIssueCard
            :active-issues-count="0"
            qa-on-duty="dr. Ardiansyah (Apoteker QA)"
          />
        </div>
      </div>

      <DeliveryHandoverFooter
        @download-log-pdf="handleDownloadLogPdf"
      />
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: Laporan & Riwayat Audit (Dokumen BAP & CPOB)                        -->
    <!-- ========================================================================= -->
    <div v-else-if="activeTab === 3" class="flex flex-col w-full space-y-6">
      <AuditSubHeaderBanner
        :tracking-number="shipment.tracking_number"
        :recipient-name="shipment.recipient_name"
        delivered-time="14 Okt 2026, 15:18 WIB"
        :final-temperature="4.4"
        :audit-transaction-id="mockBapReport.audit_transaction_id"
      />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <AuditTrailTimeline :events="mockAuditEvents" />

        <div class="flex flex-col gap-6">
          <BapCertificateCard
            :bap="mockBapReport"
            :tracking-number="shipment.tracking_number"
            @download-bap="handleDownloadBap"
            @share-telegram="handleShareTelegram"
            @print-bap="handlePrintBap"
          />

          <PackagingAssuranceCard
            :cooler-box-id="mockBapReport.cooler_box_id"
            :description="mockBapReport.cooler_box_desc"
          />
        </div>
      </div>
    </div>

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

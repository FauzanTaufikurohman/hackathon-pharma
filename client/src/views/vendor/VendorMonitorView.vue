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

function handleContactCourier() {
  toastRef.value?.show(
    'Menghubungi Satria Dimas Prasetyo',
    'Menghubungkan ke armada pendingin Satria (B 9482 PBF)...'
  )
  window.open(
    `https://wa.me/6281234567890?text=${encodeURIComponent(
      `Halo Satria, saya dari RS Medika Sehat Jakarta mengenai kiriman AWB ${shipment.value.tracking_number}.`
    )}`,
    '_blank'
  )
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
    <!-- TAB 2: Pelacakan GPS & Telemetri Suhu Live (Desain Baru)                  -->
    <!-- ========================================================================= -->
    <div v-else-if="activeTab === 2" class="flex flex-col w-full gap-6">
      <!-- 1. Hero Header & Search Bar -->
      <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold w-fit shadow-xs">
            <span class="text-[13px]">✦</span> AI-POWERED PHARMA DIAGNOSTICS
          </div>
          <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Lacak Pengiriman Smart AI</h1>
          <p class="text-sm text-slate-500">Pantau posisi armada berpendingin, integritas suhu real-time IoT, dan estimasi waktu ketibaan presisi.</p>
        </div>

        <!-- Clean White Search Card -->
        <form @submit.prevent="handleSearchAwb" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
          <div class="flex-1 flex items-center gap-3 bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5">
            <span class="material-symbols-outlined text-slate-400 text-[20px]">search</span>
            <input
              v-model="searchAwbInput"
              class="bg-transparent border-none text-sm font-bold text-slate-800 focus:outline-none w-full"
              placeholder="Masukkan Nomor AWB / Resi..."
              type="text"
            />
            <span class="text-xs text-slate-400 bg-white border border-slate-200 px-2 py-0.5 rounded font-mono">AWB</span>
          </div>
          <button
            class="bg-[#C2185B] hover:bg-[#b10045] text-white rounded-xl px-6 py-3 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-sm shrink-0 cursor-pointer"
            type="submit"
          >
            <span class="material-symbols-outlined text-[18px]">radar</span> Lacak Paket
          </button>
          <div class="hidden lg:flex items-center gap-2 text-xs text-slate-400 border-l border-slate-200 pl-4">
            <span>Riwayat:</span>
            <button
              type="button"
              @click="selectRiwayat('PHA-260104')"
              class="px-2.5 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors cursor-pointer"
            >
              # PHA-260104
            </button>
            <button
              type="button"
              @click="selectRiwayat('PHA-259810')"
              class="px-2.5 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors cursor-pointer"
            >
              # PHA-259810
            </button>
            <button
              type="button"
              @click="selectRiwayat('PHA-258442')"
              class="px-2.5 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition-colors cursor-pointer"
            >
              # PHA-258442
            </button>
          </div>
        </form>
      </div>

      <!-- 2. Prominent Magenta Status Banner -->
      <div class="bg-gradient-to-r from-[#D81E5B] to-[#AD1457] text-white p-5 sm:p-6 rounded-2xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/20 shadow-inner">
            <span class="material-symbols-outlined text-white text-[24px]">local_shipping</span>
          </div>
          <div>
            <span class="text-[11px] uppercase tracking-wider font-extrabold text-white/80 block">• STATUS PESANAN AKTIF</span>
            <h2 class="text-base sm:text-lg font-bold text-white tracking-tight leading-snug">
              DALAM PERJALANAN — Paket Sedang Diantar Menuju RS Medika Sehat
            </h2>
          </div>
        </div>

        <!-- Right Badges in Banner -->
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 backdrop-blur-sm border border-emerald-400/40 text-emerald-100 text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-300"></span> Suhu Stabil {{ (shipment.current_temperature || 4.5).toFixed(1) }}°C
          </span>
          <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-400 text-slate-900 font-bold text-xs shadow-xs">
            <span class="material-symbols-outlined text-[14px]">navigation</span> On Route (Tol Cawang)
          </span>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-semibold">
            <span class="material-symbols-outlined text-[14px]">schedule</span> Est. Tiba: 15:10 WIB (20 Mnt)
          </span>
        </div>
      </div>

      <!-- 3. Main Two-Column Tracking Viewport -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- LEFT COLUMN: Interactive GPS Map Card (7 cols) -->
        <div class="lg:col-span-7 flex flex-col relative">
          <div class="relative w-full h-[620px] rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 shadow-sm flex flex-col justify-between p-4 sm:p-6">
            <!-- Vector Map Graphic -->
            <svg class="absolute inset-0 w-full h-full object-cover pointer-events-none" preserveAspectRatio="xMidYMid slice" viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <pattern height="48" id="minimalGridNew" patternUnits="userSpaceOnUse" width="48">
                  <path d="M 48 0 L 0 0 0 48" fill="none" opacity="0.6" stroke="#E2E8F0" stroke-width="0.8"></path>
                </pattern>
                <linearGradient id="routeGradientNew" x1="0%" x2="100%" y1="100%" y2="0%">
                  <stop offset="0%" stop-color="#b10045"></stop>
                  <stop offset="55%" stop-color="#d81e5b"></stop>
                  <stop offset="100%" stop-color="#059669"></stop>
                </linearGradient>
                <filter height="120%" id="softShadowNew" width="120%" x="-10%" y="-10%">
                  <feDropShadow dx="0" dy="2" flood-opacity="0.12" stdDeviation="3"></feDropShadow>
                </filter>
              </defs>
              <!-- Map Background Fill & Street Blocks -->
              <rect fill="#F8FAFC" height="600" width="800"></rect>
              <rect fill="url(#minimalGridNew)" height="600" width="800"></rect>
              <!-- Land Blocks -->
              <path d="M 60,60 L 220,40 L 250,140 L 90,160 Z" fill="#EEF2F6" opacity="0.85"></path>
              <path d="M 320,50 L 520,30 L 540,150 L 340,170 Z" fill="#EEF2F6" opacity="0.75"></path>
              <path d="M 120,220 L 280,200 L 310,340 L 140,360 Z" fill="#EEF2F6" opacity="0.8"></path>
              <path d="M 400,200 L 620,180 L 650,330 L 420,360 Z" fill="#EEF2F6" opacity="0.8"></path>
              <path d="M 100,420 L 280,400 L 300,540 L 120,550 Z" fill="#EEF2F6" opacity="0.7"></path>
              <path d="M 380,410 L 620,390 L 650,560 L 400,570 Z" fill="#EEF2F6" opacity="0.85"></path>
              <!-- Roads -->
              <path d="M 0,120 Q 350,110 800,100" fill="none" stroke="#E2E8F0" stroke-linecap="round" stroke-width="5"></path>
              <path d="M 0,320 Q 400,300 800,280" fill="none" stroke="#E2E8F0" stroke-linecap="round" stroke-width="5"></path>
              <path d="M 0,480 Q 450,460 800,430" fill="none" stroke="#E2E8F0" stroke-linecap="round" stroke-width="4"></path>
              <path d="M 230,0 Q 240,300 250,600" fill="none" stroke="#E2E8F0" stroke-linecap="round" stroke-width="5"></path>
              <path d="M 580,0 Q 570,300 590,600" fill="none" stroke="#E2E8F0" stroke-linecap="round" stroke-width="5"></path>
              <!-- Expressway -->
              <path d="M 90,510 C 260,460 380,360 520,270 S 680,180 730,120" fill="none" stroke="#CBD5E1" stroke-linecap="round" stroke-width="8"></path>
              <!-- Route Traveled -->
              <path d="M 110,500 C 250,450 370,365 470,295" fill="none" stroke="url(#routeGradientNew)" stroke-linecap="round" stroke-width="5"></path>
              <!-- Route Remaining -->
              <path d="M 470,295 C 550,240 650,180 710,130" fill="none" opacity="0.5" stroke="#b10045" stroke-dasharray="6,6" stroke-linecap="round" stroke-width="3.5"></path>
              <!-- Origin Marker: Cikarang Hub -->
              <g transform="translate(110, 500)">
                <circle fill="#b10045" fill-opacity="0.15" r="16"></circle>
                <circle fill="#b10045" r="7"></circle>
                <circle fill="#ffffff" r="3"></circle>
              </g>
              <!-- Destination Marker: RS Medika Sehat -->
              <g transform="translate(710, 130)">
                <circle fill="#059669" fill-opacity="0.18" r="18"></circle>
                <circle fill="#059669" r="8"></circle>
                <circle fill="#ffffff" r="3.5"></circle>
              </g>
              <!-- Courier Van Marker Moving on Route -->
              <g transform="translate(470, 295)">
                <circle fill="#b10045" fill-opacity="0.18" r="24">
                  <animate attributeName="r" dur="2.4s" repeatCount="indefinite" values="16;28;16"></animate>
                  <animate attributeName="opacity" dur="2.4s" repeatCount="indefinite" values="0.4;0.05;0.4"></animate>
                </circle>
                <circle fill="#b10045" filter="url(#softShadowNew)" r="14"></circle>
                <circle fill="#ffffff" r="6"></circle>
              </g>
            </svg>

            <!-- Top Map Waypoints -->
            <div class="relative z-10 flex items-start justify-between w-full pointer-events-none">
              <!-- Hub Asal -->
              <div class="bg-white/95 backdrop-blur-sm border border-slate-200/80 px-3.5 py-2 rounded-xl shadow-xs pointer-events-auto">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">HUB ASAL</span>
                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-[#b10045]"></span> {{ shipment.origin_city || 'Cikarang Cold Hub' }} (08:45 WIB)
                </span>
              </div>
              <!-- Tujuan Pengantaran -->
              <div class="bg-white/95 backdrop-blur-sm border border-emerald-200 px-3.5 py-2 rounded-xl shadow-xs pointer-events-auto">
                <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-600 block">TUJUAN PENGANTARAN</span>
                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[15px] text-emerald-600">local_hospital</span> {{ shipment.recipient_name || 'RS Medika Sehat Jakarta' }}
                </span>
              </div>
            </div>

            <!-- Bottom Overlaid Card: Driver & Vehicle Info -->
            <div class="relative z-10 w-full bg-white/95 backdrop-blur-md border border-slate-200/80 rounded-xl p-4 sm:p-5 shadow-lg mt-auto">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                  <div class="relative shrink-0">
                    <img
                      alt="Satria Dimas Prasetyo"
                      class="w-12 h-12 rounded-full object-cover ring-2 ring-emerald-500/30 shadow-sm"
                      src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120"
                    />
                    <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-emerald-600 ring-2 ring-white flex items-center justify-center">
                      <span class="material-symbols-outlined text-white text-[10px]">check</span>
                    </span>
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">Satria Dimas Prasetyo</h3>
                      <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Armada Pendingin Khusus</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                      Plat Armada: <strong class="text-slate-800 font-semibold">B 9482 PBF</strong> • Reefer Box 2–8°C
                    </p>
                  </div>
                </div>

                <div class="flex flex-col sm:items-end justify-center pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                  <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">schedule</span>
                    <span class="text-xs sm:text-sm font-bold">Tiba ~20-25 menit (Est. 15:10 WIB)</span>
                  </div>
                  <div class="text-[12px] text-slate-500 font-medium mt-1.5 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-slate-700 font-semibold">
                      <span class="material-symbols-outlined text-[14px] text-emerald-600">speed</span> 38 km/jam
                    </span>
                    <span>•</span>
                    <span class="text-emerald-700 font-medium">Jalur Cepat Lancar</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                      <span class="material-symbols-outlined text-[12px] text-[#b10045]">inventory_2</span> AWB {{ shipment.tracking_number }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Essential Status Panel (5 cols) -->
        <aside class="lg:col-span-5 flex flex-col gap-5">
          <!-- Suhu Langsung Card -->
          <div class="w-full bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-3">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">SUHU LANGSUNG</span>
              <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Probe IoT Aktif
              </span>
            </div>
            <div class="my-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex items-baseline gap-1">
                <span class="text-6xl sm:text-7xl font-extrabold text-slate-900 tracking-tight">
                  {{ (shipment.current_temperature || 4.5).toFixed(1) }}
                </span>
                <span class="text-3xl font-bold text-slate-400">°C</span>
              </div>
              <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm self-start sm:self-auto">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                <span>Rentang Aman (2.0°C – 8.0°C)</span>
              </div>
            </div>
            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-slate-400 text-[15px]">sensors</span>
              Probe termal IoT aktif berkelanjutan • 0 deviasi terdeteksi
            </p>
          </div>

          <!-- Riwayat Suhu Chart Card -->
          <div class="w-full bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Suhu</h3>
                <p class="text-xs text-slate-400">Pembacaan ruang pendingin stabil selama transit</p>
              </div>
              <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">Standar 2–8°C</span>
            </div>

            <!-- Sparkline with Safe Band -->
            <div class="w-full h-36 bg-slate-50/80 rounded-xl p-3 border border-slate-100 flex flex-col justify-between">
              <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 360 80">
                <defs>
                  <linearGradient id="safeBandNew" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#10B981" stop-opacity="0.12"></stop>
                    <stop offset="100%" stop-color="#10B981" stop-opacity="0.04"></stop>
                  </linearGradient>
                </defs>
                <rect fill="url(#safeBandNew)" height="58" rx="4" width="360" x="0" y="10"></rect>
                <line opacity="0.4" stroke="#10B981" stroke-dasharray="3,3" stroke-width="0.8" x1="0" x2="360" y1="10" y2="10"></line>
                <line opacity="0.4" stroke="#10B981" stroke-dasharray="3,3" stroke-width="0.8" x1="0" x2="360" y1="68" y2="68"></line>
                <path d="M 10,43 C 55,41 75,44 110,42 S 165,45 200,43 S 255,41 290,44 S 325,42 350,42" fill="none" stroke="#059669" stroke-linecap="round" stroke-width="2.5"></path>
                <circle cx="10" cy="43" fill="#059669" r="3"></circle>
                <circle cx="110" cy="42" fill="#059669" r="3"></circle>
                <circle cx="200" cy="43" fill="#059669" r="3"></circle>
                <circle cx="290" cy="44" fill="#059669" r="3"></circle>
                <circle cx="350" cy="42" fill="#059669" r="4.5"></circle>
                <circle cx="350" cy="42" fill="#059669" opacity="0.25" r="8"></circle>
              </svg>
              <div class="flex justify-between text-[11px] font-semibold text-slate-400 px-1 pt-1 border-t border-slate-200/60">
                <span>09:00</span>
                <span>09:30</span>
                <span>10:00</span>
                <span>10:30</span>
                <span class="text-emerald-700 font-bold">Sekarang</span>
              </div>
            </div>
          </div>

          <!-- Action Button & WhatsApp Notification Card -->
          <div class="w-full bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col gap-3.5">
            <!-- Primary Contact Button -->
            <button
              type="button"
              @click="handleContactCourier"
              class="w-full py-3 px-4 rounded-xl border-2 border-[#b10045] text-[#b10045] hover:bg-[#b10045]/5 font-bold text-sm flex items-center justify-center gap-2 transition-colors cursor-pointer"
            >
              <span class="material-symbols-outlined text-[18px]">call</span>
              <span>Hubungi Satria (Telepon / Chat)</span>
            </button>

            <!-- Notification Toggle -->
            <div class="flex items-center justify-between pt-2 px-1">
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">chat</span>
                <div>
                  <p class="text-xs font-bold text-slate-800">Notifikasi Langsung WhatsApp Aktif</p>
                  <p class="text-[11px] text-slate-400">Pembaruan dikirim langsung ke apoteker bertugas</p>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  class="sr-only peer"
                  v-model="waNotificationEnabled"
                  @change="handleToggleWa"
                />
                <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
            </div>
          </div>
        </aside>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: Laporan & Bukti Serah Terima (Desain Baru)                         -->
    <!-- ========================================================================= -->
    <div v-else-if="activeTab === 3" class="flex flex-col w-full space-y-6">
      <!-- 1. Hero Header & Search Bar -->
      <div class="space-y-4">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold text-xs">
          <span class="material-symbols-outlined text-[14px]">auto_awesome</span> AI-POWERED PHARMA DIAGNOSTICS
        </div>
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Lacak Pengiriman Smart AI</h1>
          <p class="text-sm text-slate-500 mt-1 max-w-3xl">
            Pengiriman telah selesai dan terverifikasi. Seluruh dokumen Berita Acara Penerimaan (BAP) dan sertifikat CDOB telah diterbitkan secara sah.
          </p>
        </div>

        <!-- Clean Search Card -->
        <form @submit.prevent="handleSearchAwb" class="bg-white rounded-2xl border border-slate-200 p-2.5 sm:p-3 shadow-sm flex flex-col sm:flex-row items-center gap-2.5">
          <div class="flex-1 flex items-center gap-3 px-3 w-full">
            <span class="material-symbols-outlined text-slate-400 text-[20px]">tag</span>
            <input
              v-model="searchAwbInput"
              class="w-full bg-transparent border-none text-slate-800 font-mono font-bold text-sm focus:outline-none focus:ring-0 p-0"
              type="text"
              placeholder="Masukkan Nomor AWB..."
            />
          </div>
          <button
            class="w-full sm:w-auto bg-[#D81E5B] hover:bg-[#b10045] text-white rounded-xl px-6 py-2.5 font-semibold text-xs flex items-center justify-center gap-2 transition-all shadow-sm cursor-pointer"
            type="submit"
          >
            <span class="material-symbols-outlined text-[16px]">search</span> Lacak Paket
          </button>
        </form>
      </div>

      <!-- 2. Prominent Status Banner (Emerald Gradient) -->
      <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-5 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[28px] text-white">task_alt</span>
          </div>
          <div>
            <div class="text-[11px] font-bold tracking-wider uppercase text-emerald-100">• STATUS PESANAN</div>
            <div class="text-base font-extrabold text-white mt-0.5">
              SUDAH DITERIMA — Paket Telah Diserahterimakan dengan Aman &amp; Sesuai Standar CPOB
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="bg-white/20 backdrop-blur-md text-white text-xs font-semibold px-3 py-1 rounded-full">100% CDOB Compliance</span>
          <span class="bg-amber-400 text-slate-900 font-bold text-xs px-3 py-1 rounded-full shadow-sm">Tuntas Terverifikasi</span>
          <span class="bg-white/10 text-emerald-100 text-xs font-medium px-3 py-1 rounded-full">Selesai: 14 Okt, 11:30 WIB</span>
        </div>
      </div>

      <!-- 3. 3-Point Summary Card (Buku Catatan Serah Terima Utama) -->
      <section class="rounded-2xl bg-white border border-slate-200 p-6 md:p-8 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">BUKU CATATAN SERAH TERIMA UTAMA</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-slate-100">
          <div class="pt-4 md:pt-0 md:first:pt-0 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
              <span class="material-symbols-outlined text-[20px]">person_check</span>
            </div>
            <div>
              <span class="text-xs font-medium text-slate-500 block">Diterima Oleh</span>
              <span class="text-base font-bold text-slate-900 block mt-0.5">Apt. Sarah Johnson, S.Farm</span>
              <span class="text-xs text-slate-500 block mt-0.5">Kepala Instalasi Farmasi RS Medika Sehat</span>
            </div>
          </div>

          <div class="pt-4 md:pt-0 md:pl-6 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
              <span class="material-symbols-outlined text-[20px]">schedule</span>
            </div>
            <div>
              <span class="text-xs font-medium text-slate-500 block">Waktu Serah Terima</span>
              <span class="text-base font-bold text-slate-900 block mt-0.5">14 Okt 2026, 11:30 WIB</span>
              <span class="text-xs text-slate-500 block mt-0.5">Tersinkronisasi NTP (WIB)</span>
            </div>
          </div>

          <div class="pt-4 md:pt-0 md:pl-6 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
              <span class="material-symbols-outlined text-[20px]">ac_unit</span>
            </div>
            <div>
              <span class="text-xs font-medium text-slate-500 block">Kepatuhan Suhu Termal</span>
              <span class="text-base font-bold text-emerald-700 block mt-0.5">100% Terjaga pada 2.0°C – 8.0°C</span>
              <span class="text-xs text-slate-500 block mt-0.5">Nol deviasi suhu tercatat selama transit</span>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. Proof Photo & Dossier Actions (12 cols) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Left: Bukti Serah Terima & e-POD (5 cols) -->
        <div class="lg:col-span-5 rounded-2xl bg-white border border-slate-200 p-6 md:p-7 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-400">BUKTI SERAH TERIMA &amp; e-POD</span>
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                <span class="material-symbols-outlined text-[13px]">lock</span> Segel Terverifikasi
              </span>
            </div>

            <!-- Verification Photo -->
            <div class="relative w-full h-48 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 mb-4 group">
              <img
                alt="Hospital Airlock Handover Verification"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                src="https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&q=80&w=800"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent flex items-end p-3.5">
                <div class="text-white">
                  <div class="text-xs font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Inspeksi Ruang Antara (Airlock) Ruang Dingin
                  </div>
                  <div class="text-[11px] text-slate-200">Probe Suhu: +4.2°C • Segel Utuh (#TS-7821)</div>
                </div>
              </div>
            </div>

            <!-- Digital Signature Box -->
            <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3.5">
              <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium">Tanda Tangan Digital Apoteker</span>
                <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-[12px]">verified</span> Sah / Valid
                </span>
              </div>
              <div class="h-14 flex items-center justify-center">
                <svg class="w-56 h-10 text-slate-800" fill="none" viewBox="0 0 320 80" xmlns="http://www.w3.org/2000/svg">
                  <path d="M18 42C35 40 50 15 62 24C74 33 60 62 48 60C38 58 45 42 75 38C105 34 118 18 130 28C142 38 120 55 140 48C160 41 185 30 198 36C211 42 200 58 220 50C240 42 265 25 285 32C295 35 305 45 312 40" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                  <path d="M70 54L180 50" stroke="currentColor" stroke-dasharray="2 4" stroke-linecap="round" stroke-width="1.8"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Penandatangan: Apt. Sarah Johnson</span>
            <span class="font-mono text-[11px]">SIPA: 19890512/SIPA/2022</span>
          </div>
        </div>

        <!-- Right: Certificate & Actions (7 cols) -->
        <div class="lg:col-span-7 rounded-2xl bg-white border border-slate-200 p-6 md:p-8 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-2 text-[#b10045] mb-2">
              <span class="material-symbols-outlined text-[20px]">verified</span>
              <span class="text-xs font-bold uppercase tracking-wider">DOKUMEN AUDIT RESMI BERSERTIFIKASI</span>
            </div>
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 leading-snug">
              Sertifikat Integritas Rantai Pengawasan &amp; Suhu Anteraja Pharma
            </h2>
            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
              Dokumen resmi yang mengesahkan kepatuhan pendinginan aktif berkelanjutan, keutuhan segel antipengrusakan, dan verifikasi penerimaan rumah sakit sesuai regulasi CDOB BPOM.
            </p>

            <div class="mt-4 flex flex-wrap gap-2 text-xs">
              <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium border border-slate-200">
                ID Lot: <strong>{{ shipment.batch_number || 'VAX-INF-09941' }}</strong>
              </span>
              <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium border border-slate-200">
                Kode Dok: <strong>COC-AP-2026-X88</strong>
              </span>
              <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-medium border border-emerald-200 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">check</span> Tervalidasi BPOM
              </span>
            </div>
          </div>

          <div class="mt-8 pt-6 border-t border-slate-100 space-y-3">
            <button
              type="button"
              @click="handleDownloadLogPdf"
              class="w-full py-3.5 px-6 rounded-xl bg-[#D81E5B] hover:bg-[#b10045] text-white font-bold text-sm shadow-md shadow-[#D81E5B]/20 flex items-center justify-center gap-2.5 transition-all active:scale-[0.99] cursor-pointer"
            >
              <span class="material-symbols-outlined text-[20px]">download</span>
              Unduh Laporan Kepatuhan Resmi BPOM (PDF)
            </button>
            <div class="flex flex-col sm:flex-row items-center gap-3">
              <button
                type="button"
                @click="handleShareTelegram('+62 811-9876-5432')"
                class="w-full sm:flex-1 py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer"
              >
                <span class="material-symbols-outlined text-[18px]">chat</span>
                Kirim Laporan PDF ke WhatsApp
              </button>
              <button
                type="button"
                @click="handlePrintBap"
                class="w-full sm:w-auto py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer"
              >
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak Ringkasan Arsip
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

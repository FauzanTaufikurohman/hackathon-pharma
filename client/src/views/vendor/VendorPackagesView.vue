<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import NotificationToast from '../../components/vendor/NotificationToast.vue'

interface PackageItem {
  id: string
  awb: string
  title: string
  route: string
  routeType: 'transit' | 'qc' | 'delivered'
  statusBadge: string
  statusType: 'transit' | 'qc' | 'near' | 'delivered'
  tempValue: string
  tempStatus: string
  etaOrStatus: string
  etaType: 'eta' | 'ready' | 'verified'
  actionLabel: string
  actionIcon: string
  actionColor: string
  targetScreen: number
}

const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)
const activeFilter = ref<'all' | 'transit' | 'qc' | 'delivered'>('all')
const searchQuery = ref<string>('')

const packages = ref<PackageItem[]>([
  {
    id: '1',
    awb: '#100098421088712',
    title: 'Insulin Reguler & Biologis (50 Vial)',
    route: 'Cikarang Cold Hub → RS Medika Sehat (Gd. Farmasi B)',
    routeType: 'transit',
    statusBadge: 'Dalam Perjalanan',
    statusType: 'transit',
    tempValue: '4.5°C',
    tempStatus: '(Aman)',
    etaOrStatus: '~25 Menit',
    etaType: 'eta',
    actionLabel: 'Lacak Live',
    actionIcon: 'arrow_forward',
    actionColor: 'bg-[#D81E5B] hover:bg-[#b10045] text-white shadow-pink-100',
    targetScreen: 1,
  },
  {
    id: '2',
    awb: '#100098421099415',
    title: 'Vaksin Polio Oral & Pediatrik (120 Botol)',
    route: 'Staging Hub Jaktim → Instalasi Farmasi RS Medika',
    routeType: 'qc',
    statusBadge: 'QC Staging Gudang',
    statusType: 'qc',
    tempValue: '4.2°C',
    tempStatus: '(Stabil)',
    etaOrStatus: 'Siap Kirim',
    etaType: 'ready',
    actionLabel: 'Pantau Gudang',
    actionIcon: 'inventory',
    actionColor: 'bg-[#F59E0B] hover:bg-[#d97706] text-white',
    targetScreen: 1,
  },
  {
    id: '3',
    awb: '#100098421077321',
    title: 'Serum Anti-Bisa Ular & Plasma Dingin (20 Pack)',
    route: 'Bio Farma Hub → Farmasi IGD RS Medika',
    routeType: 'transit',
    statusBadge: 'Mendekati Lokasi',
    statusType: 'near',
    tempValue: '3.9°C',
    tempStatus: '(Aman)',
    etaOrStatus: '~8 Menit',
    etaType: 'eta',
    actionLabel: 'Pelacakan GPS',
    actionIcon: 'navigation',
    actionColor: 'bg-emerald-600 hover:bg-emerald-700 text-white',
    targetScreen: 1,
  },
  {
    id: '4',
    awb: '#100098421066109',
    title: 'Vaksin Influenza Flubio Monovalen (80 Vial)',
    route: 'Diterima oleh Apt. Sarah Johnson, S.Farm (Gd. B Lt. 1)',
    routeType: 'delivered',
    statusBadge: 'Tiba Hari Ini (08:30 WIB)',
    statusType: 'delivered',
    tempValue: '4.1°C',
    tempStatus: '(Nol Deviasi)',
    etaOrStatus: 'Terverifikasi',
    etaType: 'verified',
    actionLabel: 'Berita Acara POD',
    actionIcon: 'description',
    actionColor: 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200',
    targetScreen: 1,
  },
])

// Counts
const counts = computed(() => ({
  all: packages.value.length,
  transit: packages.value.filter(p => p.routeType === 'transit').length,
  qc: packages.value.filter(p => p.routeType === 'qc').length,
  delivered: packages.value.filter(p => p.routeType === 'delivered').length,
}))

// Filtered packages
const displayedPackages = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return packages.value.filter(item => {
    // Category tab filter
    const matchesCategory =
      activeFilter.value === 'all' || item.routeType === activeFilter.value

    // Search query
    const text = `${item.awb} ${item.title} ${item.route}`.toLowerCase()
    const matchesSearch = !query || text.includes(query)

    return matchesCategory && matchesSearch
  })
})

function handleSyncSensors() {
  toastRef.value?.show(
    'Sinkronisasi Sensor Berhasil',
    'Telemetri IoT 4 kargo farmasi telah dimutakhirkan (transmisi 10s).'
  )
}

function handleDownloadRekap() {
  toastRef.value?.show(
    'Mengunduh Rekap CDOB BPOM',
    'Menyiapkan berkas log audit rantai dingin harian (PDF/A)...'
  )
}

function handleActionClick(item: PackageItem) {
  toastRef.value?.show(
    `Membuka Halaman Pantau`,
    `Mengalihkan ke ${item.awb} (${item.title})...`
  )
  setTimeout(() => {
    router.push({
      path: `/vendor/monitor/${encodeURIComponent(item.awb)}`,
      query: { screen: '1' },
    })
  }, 250)
}

function handleOpenExcursionGuide() {
  toastRef.value?.show(
    'Pedoman Excursion Suhu BPOM',
    'Membuka panduan mitigasi deviasi suhu CPOB/CDOB 2024.3...'
  )
}
</script>
<template>
  <div class="w-full flex flex-col gap-6">
    <!-- 1. Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FCE7F3] text-[#D81E5B] text-xs font-bold uppercase tracking-wider mb-2.5">
          <span>✦</span> Portal Konsinyasi Aktif
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
          Daftar Kiriman Farmasi Aktif
        </h1>
        <p class="text-slate-500 text-sm mt-1.5 max-w-2xl leading-relaxed">
          Pantau integritas rantai dingin (<span class="font-semibold text-slate-700">cold-chain</span>), telemetri sensor suhu IoT real-time, serta kalkulasi estimasi kedatangan kargo obat &amp; vaksin untuk <span class="font-semibold text-slate-800">RS Medika Sehat Jakarta</span>.
        </p>
      </div>

      <div class="flex items-center gap-3 shrink-0">
        <button
          type="button"
          @click="handleSyncSensors"
          class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-sm transition-all shadow-xs cursor-pointer"
        >
          <span class="material-symbols-outlined text-[18px] text-[#D81E5B]">sync</span>
          <span>Sinkronisasi Sensor</span>
        </button>

        <button
          type="button"
          @click="handleDownloadRekap"
          class="inline-flex items-center gap-2 bg-[#D81E5B] hover:bg-[#b10045] text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-all shadow-sm shadow-pink-200 cursor-pointer"
        >
          <span class="material-symbols-outlined text-[18px]">download</span>
          <span>Unduh Rekap CDOB (PDF)</span>
        </button>
      </div>
    </div>

    <!-- 2. Summary Metric Cards (3 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Card 1: Paket Berjalan -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paket Berjalan</span>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-extrabold text-slate-900">4 Paket</span>
            <span class="text-xs text-[#D81E5B] font-bold bg-[#FCE7F3] px-2 py-0.5 rounded-full">Aktif</span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-[#FCE7F3] text-[#D81E5B] flex items-center justify-center">
          <span class="material-symbols-outlined text-[24px]">local_shipping</span>
        </div>
      </div>

      <!-- Card 2: Rata-rata Suhu Rantai Dingin -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-rata Suhu Rantai Dingin</span>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-extrabold text-slate-900">4.2°C</span>
            <span class="text-xs text-emerald-700 font-bold bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full">
              Aman 2°C - 8°C
            </span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-[24px]">device_thermostat</span>
        </div>
      </div>

      <!-- Card 3: Deviasi & Insiden Suhu -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deviasi &amp; Insiden Suhu</span>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-extrabold text-slate-900">0 Kasus</span>
            <span class="text-xs text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">
              100% CDOB Patuh
            </span>
          </div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-[24px]">verified</span>
        </div>
      </div>
    </div>

    <!-- 3. Vibrant Magenta Gradient Priority Banner -->
    <div class="bg-gradient-to-r from-[#D81E5B] to-[#AD1457] text-white p-5 rounded-2xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-white text-[24px]">health_and_safety</span>
        </div>
        <div>
          <div class="text-[11px] font-bold tracking-wider text-pink-200 uppercase mb-0.5">• Monitor Prioritas Distribusi</div>
          <div class="text-lg font-bold text-white">4 Kargo Farmasi Terjadwal Tiba Sesuai Standar Suhu CDOB</div>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <span class="bg-amber-400 text-slate-900 font-bold px-3 py-1 rounded-full text-xs shadow-sm">
          1 Kargo Dalam Perjalanan (~25 Mnt)
        </span>
        <span class="bg-white/20 text-white font-semibold px-3 py-1 rounded-full text-xs">
          Suhu Terjaga 100%
        </span>
        <span class="bg-white/20 text-white font-semibold px-3 py-1 rounded-full text-xs">
          Verifikasi IoT Aktif
        </span>
      </div>
    </div>

    <!-- 4. Search & Filter Tab Card -->
    <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <div class="relative flex-1">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">
          search
        </span>
        <input
          v-model="searchQuery"
          class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-800 placeholder:text-slate-400 text-sm outline-none border border-slate-100 focus:bg-white focus:border-[#D81E5B] transition-all"
          placeholder="Cari nomor resi, nama obat, atau tujuan..."
          type="text"
        />
      </div>

      <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
        <button
          type="button"
          @click="activeFilter = 'all'"
          :class="activeFilter === 'all'
            ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-100 font-semibold'"
          class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition-colors cursor-pointer"
        >
          Semua ({{ counts.all }})
        </button>

        <button
          type="button"
          @click="activeFilter = 'transit'"
          :class="activeFilter === 'transit'
            ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-100 font-semibold'"
          class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition-colors cursor-pointer"
        >
          Dalam Perjalanan ({{ counts.transit }})
        </button>

        <button
          type="button"
          @click="activeFilter = 'qc'"
          :class="activeFilter === 'qc'
            ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-100 font-semibold'"
          class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition-colors cursor-pointer"
        >
          Pemeriksaan QC ({{ counts.qc }})
        </button>

        <button
          type="button"
          @click="activeFilter = 'delivered'"
          :class="activeFilter === 'delivered'
            ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-100 font-semibold'"
          class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition-colors cursor-pointer"
        >
          Tiba Hari Ini ({{ counts.delivered }})
        </button>
      </div>
    </div>

    <!-- 5. Refined Shipment List Cards -->
    <div class="flex flex-col gap-4">
      <!-- Empty state if no results -->
      <div
        v-if="displayedPackages.length === 0"
        class="bg-white rounded-2xl border border-slate-100 p-8 text-center flex flex-col items-center justify-center gap-2"
      >
        <span class="material-symbols-outlined text-[48px] text-slate-300">search_off</span>
        <h3 class="text-base font-bold text-slate-800">Tidak ada kiriman yang cocok</h3>
        <p class="text-xs text-slate-500 max-w-sm">
          Silakan coba kata kunci lain atau ubah tab filter kategori di atas.
        </p>
      </div>

      <!-- Package Cards -->
      <div
        v-for="item in displayedPackages"
        :key="item.id"
        @click="handleActionClick(item)"
        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:shadow-md transition-shadow flex flex-col md:flex-row md:items-center justify-between gap-6 cursor-pointer"
      >
        <!-- Left: Status pill, AWB, Title, Route -->
        <div class="flex flex-col gap-2.5 flex-1">
          <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Status Badge based on type -->
            <span
              v-if="item.statusType === 'transit'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FCE7F3] text-[#D81E5B] text-xs font-bold"
            >
              <span class="w-2 h-2 rounded-full bg-[#D81E5B] animate-pulse"></span>
              {{ item.statusBadge }}
            </span>

            <span
              v-else-if="item.statusType === 'qc'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold border border-amber-100"
            >
              <span class="w-2 h-2 rounded-full bg-[#F59E0B]"></span>
              {{ item.statusBadge }}
            </span>

            <span
              v-else-if="item.statusType === 'near'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-100"
            >
              <span class="w-2 h-2 rounded-full bg-emerald-600 animate-bounce"></span>
              {{ item.statusBadge }}
            </span>

            <span
              v-else-if="item.statusType === 'delivered'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-800 text-xs font-bold border border-blue-100"
            >
              <span class="material-symbols-outlined text-[15px] text-blue-600">check_circle</span>
              {{ item.statusBadge }}
            </span>

            <!-- AWB pill -->
            <span class="text-xs text-slate-500 font-mono bg-slate-50 px-2.5 py-0.5 rounded-md border border-slate-100">
              AWB {{ item.awb }}
            </span>
          </div>

          <h3 class="text-lg font-bold text-slate-900">{{ item.title }}</h3>

          <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span
              v-if="item.statusType === 'qc'"
              class="material-symbols-outlined text-[16px] text-amber-500"
            >
              warehouse
            </span>
            <span
              v-else-if="item.statusType === 'delivered'"
              class="material-symbols-outlined text-[16px] text-blue-600"
            >
              person_check
            </span>
            <span
              v-else
              class="material-symbols-outlined text-[16px]"
              :class="item.statusType === 'near' ? 'text-emerald-600' : 'text-[#D81E5B]'"
            >
              near_me
            </span>

            <span>{{ item.route }}</span>
          </div>
        </div>

        <!-- Center: Temperature & ETA -->
        <div class="flex items-center gap-8 py-3 md:py-0 border-y md:border-y-0 md:border-x border-slate-100 md:px-8 shrink-0">
          <div class="flex flex-col gap-1">
            <span class="text-xs text-slate-400 font-medium">
              {{ item.statusType === 'qc' ? 'Suhu Ruang' : item.statusType === 'delivered' ? 'Suhu Kedatangan' : 'Suhu Sensor' }}
            </span>
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span class="text-base font-extrabold text-slate-900">{{ item.tempValue }}</span>
              <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                {{ item.tempStatus }}
              </span>
            </div>
          </div>

          <div class="flex flex-col gap-1">
            <span class="text-xs text-slate-400 font-medium">
              {{ item.statusType === 'qc' ? 'Kesiapan Dispatch' : item.statusType === 'delivered' ? 'Status Serah Terima' : 'Estimasi Tiba' }}
            </span>
            <span
              v-if="item.etaType === 'eta'"
              :class="item.statusType === 'near'
                ? 'text-emerald-700 bg-emerald-50 border border-emerald-100'
                : 'text-[#D81E5B] bg-[#FCE7F3]'"
              class="text-sm font-extrabold px-2.5 py-0.5 rounded-lg"
            >
              {{ item.etaOrStatus }}
            </span>
            <span
              v-else-if="item.etaType === 'ready'"
              class="text-sm font-extrabold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-lg border border-amber-100"
            >
              {{ item.etaOrStatus }}
            </span>
            <span
              v-else-if="item.etaType === 'verified'"
              class="text-sm font-extrabold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-100"
            >
              {{ item.etaOrStatus }}
            </span>
          </div>
        </div>

        <!-- Right: Action Button -->
        <div class="shrink-0">
          <button
            type="button"
            @click="handleActionClick(item)"
            :class="item.actionColor"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 font-semibold text-sm px-5 py-2.5 rounded-xl transition-all shadow-xs cursor-pointer active:scale-[0.98]"
          >
            <span>{{ item.actionLabel }}</span>
            <span class="material-symbols-outlined text-[18px]">{{ item.actionIcon }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 6. CDOB Compliance Guidance Footnote Card -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mt-2">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#D81E5B] flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[26px]">shield_with_heart</span>
        </div>
        <div>
          <h4 class="text-sm font-bold text-slate-900">Prosedur Serah Terima Klinis Berstandar CDOB BPOM RI</h4>
          <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
            Klik kartu pengiriman mana pun di atas untuk membuka layar peninjauan spesifik: verifikasi kalibrasi sensor pra-kirim, inspeksi peta rute telemetri aktif, atau unduhan berita acara serah terima resmi (e-POD).
          </p>
        </div>
      </div>
      <button
        type="button"
        @click="handleOpenExcursionGuide"
        class="shrink-0 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 px-4 py-2.5 rounded-xl transition-colors cursor-pointer"
      >
        Panduan Excursion BPOM
      </button>
    </div>

    <!-- Toast Notification Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

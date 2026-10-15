<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { mockShipments } from '../../data/mockShipments'
import NotificationToast from '../../components/vendor/NotificationToast.vue'

const router = useRouter()
const toastRef = ref<InstanceType<typeof NotificationToast> | null>(null)
const searchInput = ref('')

function handleSearch() {
  const query = searchInput.value.trim().toLowerCase().replace(/^awb\s*/i, '').replace(/^#/, '')
  if (!query) {
    router.push('/vendor/monitor/%23PHA-260104?screen=1')
    return
  }

  const found = mockShipments.find(s =>
    s.tracking_number.toLowerCase().replace(/^#/, '').includes(query) ||
    s.item_summary.toLowerCase().includes(query)
  )

  if (found) {
    toastRef.value?.show(
      'Kargo Ditemukan!',
      `Memuat data ${found.tracking_number} (${found.item_summary}).`
    )
    router.push(`/vendor/monitor/${encodeURIComponent(found.tracking_number)}?screen=1`)
  } else {
    toastRef.value?.show(
      'Melacak Kargo',
      `Menghubungkan ke telemetri kargo #${query}...`
    )
    router.push(`/vendor/monitor/${encodeURIComponent(query)}?screen=1`)
  }
  priority_shipment: LiveShipment | null
}

function openMonitor(screen = 1, awb = '#PHA-260104') {
  router.push(`/vendor/monitor/${encodeURIComponent(awb)}?screen=${screen}`)
}

function handlePickUpSpecial() {
  toastRef.value?.show(
    'Permintaan Pick-Up Khusus',
    'Menghubungkan ke armada Reefer Box 2–8°C terdekat untuk penjadwalan penjemputan vaksin.'
  )
}

function handleContactHotline() {
  toastRef.value?.show(
    'Hotline Farmasi 24 Jam',
    'Menghubungkan ke Apoteker Konsultan Anteraja Pharma (021 - 5066 - 3333)...'
  )
  window.open('https://wa.me/6281119603333?text=Halo%20Apoteker%20Anteraja%20Pharma,%20saya%20dari%20RS%20Medika%20Sehat%20butuh%20konsultasi%20pengiriman%20vaksin.', '_blank')
}
</script>
<template>
  <div class="flex flex-col w-full space-y-8">
    <!-- 1. Hero & Primary Search Area -->
    <section class="w-full">
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-fixed/30 via-surface-container-lowest to-secondary-container/20 p-6 sm:p-8 lg:p-10 border border-primary-fixed/40 shadow-sm">
        <div class="absolute -right-16 -top-20 w-80 h-80 rounded-full bg-primary-container/10 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-20 w-80 h-80 rounded-full bg-secondary-container/30 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-4xl flex flex-col gap-4">
          <!-- Verification Badges -->
          <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-lowest text-primary font-label-sm text-xs shadow-xs border border-primary/20 font-bold">
              <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
              Sertifikasi CDOB BPOM &amp; WHO TRS 961
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold">
              <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
              Real-Time 2°C – 8°C Active
            </span>
          </div>

          <!-- Hero Greeting -->
          <div class="flex flex-col gap-1.5">
            <h1 class="font-display-lg text-2xl sm:text-3xl lg:text-[36px] lg:leading-[44px] text-on-surface tracking-tight font-bold">
              Selamat Datang, <span class="text-primary">Instalasi Farmasi RS Medika Sehat</span>
            </h1>
            <p class="font-body-lg text-sm sm:text-base text-on-surface-variant max-w-2xl font-medium">
              Pantau kargo biologis, vaksin, dan obat rantai dingin secara presisi real-time.
            </p>
          </div>

          <!-- AWB Search Form -->
          <div class="mt-2 flex flex-col gap-2 w-full">
            <form
              @submit.prevent="handleSearch"
              class="relative flex items-center w-full bg-surface-container-lowest rounded-2xl p-1.5 shadow-md border border-primary/20 hover:border-primary transition-all"
            >
              <div class="pl-4 pr-2 text-primary flex items-center">
                <span class="material-symbols-outlined text-[24px]">sensors</span>
              </div>
              <input
                v-model="searchInput"
                class="w-full py-3.5 px-2 bg-transparent text-on-surface placeholder:text-outline font-body-md text-sm sm:text-base focus:outline-none"
                placeholder="Lacak AWB farmasi (cth: #100098421088712) atau nama sediaan obat..."
                type="text"
              />
              <button
                class="shrink-0 flex items-center gap-1.5 px-5 sm:px-6 py-3 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-bold text-sm transition-all duration-200 shadow-sm active:scale-[0.98] cursor-pointer"
                type="submit"
              >
                <span>Lacak Kargo</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. Ringkasan Status Cepat (3 Clean Summary Cards) -->
    <section class="w-full space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h2 class="font-headline-md text-lg sm:text-xl font-bold text-on-surface">Ringkasan Operasional Hari Ini</h2>
          <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant">Status kargo rantai dingin yang ditujukan ke fasilitas Anda</p>
        </div>
        <span class="text-xs text-on-surface-variant flex items-center gap-1 bg-surface-container-low px-3 py-1 rounded-full w-fit">
          <span class="material-symbols-outlined text-[14px]">sync</span>
          Sinkronisasi otomatis (tiap 60d)
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Paket Berjalan -->
        <div class="group relative flex flex-col justify-between bg-gradient-to-br from-primary-fixed/20 to-surface-container-lowest rounded-2xl p-6 border border-primary/20 shadow-sm hover:shadow-md transition-all">
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
              <span class="font-label-md text-xs uppercase tracking-wider text-primary font-bold">Paket Berjalan</span>
              <div class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center shadow-xs">
                <span class="material-symbols-outlined text-[20px]">local_shipping</span>
              </div>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="font-display-lg text-3xl font-bold text-primary">4</span>
              <span class="font-headline-sm text-sm font-semibold text-on-surface">Kiriman Aktif</span>
            </div>
            <div class="flex flex-wrap gap-1.5 mt-2">
              <span class="px-2 py-0.5 rounded-md bg-surface-container-lowest text-primary text-[11px] font-semibold border border-primary/20">2 Transit</span>
              <span class="px-2 py-0.5 rounded-md bg-surface-container-lowest text-on-surface-variant text-[11px]">1 QC</span>
              <span class="px-2 py-0.5 rounded-md bg-secondary-container text-on-secondary-container text-[11px] font-bold">1 Dekat Faskes</span>
            </div>
          </div>
          <div class="pt-4 mt-4 border-t border-primary/10 flex items-center justify-between">
            <router-link
              to="/paket-saya"
              class="text-xs text-primary hover:text-primary-container inline-flex items-center gap-1 font-bold group-hover:translate-x-0.5 transition-transform"
            >
              <span>Buka Paket Saya</span>
              <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </router-link>
            <span class="text-xs text-outline">Terjadwal hari ini</span>
          </div>
        </div>

        <!-- Card 2: Integritas Cold-Chain -->
        <div class="relative flex flex-col justify-between bg-gradient-to-br from-secondary-container/20 to-surface-container-lowest rounded-2xl p-6 border border-secondary/20 shadow-sm hover:shadow-md transition-all">
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
              <span class="font-label-md text-xs uppercase tracking-wider text-secondary font-bold">Integritas Cold-Chain</span>
              <div class="w-10 h-10 rounded-xl bg-secondary text-on-secondary flex items-center justify-center shadow-xs">
                <span class="material-symbols-outlined text-[20px]">thermostat</span>
              </div>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="font-display-lg text-3xl font-bold text-secondary">100%</span>
              <span class="font-headline-sm text-sm font-semibold text-on-surface">CDOB Standar</span>
            </div>
            <div class="flex flex-col gap-1.5 mt-1">
              <div class="w-full bg-surface-container-high h-2.5 rounded-full overflow-hidden flex">
                <div class="bg-secondary h-full rounded-full w-full"></div>
              </div>
              <div class="flex items-center justify-between text-[11px]">
                <span class="text-on-surface font-medium">Suhu Rata: <strong class="text-secondary font-bold">4.2°C</strong></span>
                <span class="text-secondary font-bold">0 Insiden</span>
              </div>
            </div>
          </div>
          <div class="pt-4 mt-4 border-t border-secondary/10 flex items-center justify-between">
            <span class="inline-flex items-center gap-1 text-xs text-secondary font-bold">
              <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
              ISO 17025 Aktif
            </span>
            <span class="text-xs text-outline font-medium">Target 2–8°C</span>
          </div>
        </div>

        <!-- Card 3: Penerimaan Terdekat -->
        <div class="relative flex flex-col justify-between bg-gradient-to-br from-surface-container-high to-surface-container-lowest rounded-2xl p-6 border border-surface-variant shadow-sm hover:shadow-md transition-all">
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
              <span class="font-label-md text-xs uppercase tracking-wider text-on-surface font-bold">Penerimaan Terdekat</span>
              <div class="w-10 h-10 rounded-xl bg-primary-container text-on-primary flex items-center justify-center shadow-xs">
                <span class="material-symbols-outlined text-[20px]">schedule</span>
              </div>
            </div>
            <div class="flex flex-col mt-1">
              <div class="flex items-baseline gap-1.5">
                <span class="font-headline-lg text-xl font-bold text-on-surface">11:15 WIB</span>
                <span class="text-xs text-primary font-bold">(~25 Mnt)</span>
              </div>
              <p class="text-sm text-on-surface font-semibold truncate mt-0.5">Vaksin Flubio Monovalen</p>
            </div>
            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs">
              <span class="material-symbols-outlined text-[15px] text-secondary">local_shipping</span>
              <span>Reefer Satria Dimas (B 9281 PFA)</span>
            </div>
          </div>
          <div class="pt-4 mt-4 border-t border-surface-variant/40 flex items-center justify-between">
            <span class="text-xs text-on-surface font-medium">Suhu: <span class="font-bold text-secondary">3.8°C</span></span>
            <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[11px] font-bold">Prioritas Dingin</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. In-Transit Snapshot with Map and Cold Storage Showcase -->
    <section class="w-full">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Active Fleet Telemetry Map Card (7 cols) -->
        <div class="lg:col-span-7 bg-surface-container-lowest rounded-3xl p-6 md:p-7 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-secondary-container/30 flex items-center justify-center text-secondary">
                  <span class="material-symbols-outlined text-[18px]">near_me</span>
                </div>
                <div>
                  <h3 class="font-headline-sm text-base font-bold text-on-surface">Radar Armada Menuju Faskes Anda</h3>
                  <p class="font-body-sm text-xs text-on-surface-variant">Estimasi waktu tempuh diperbarui via GPS Reefer Tracker</p>
                </div>
              </div>
              <span class="text-xs text-secondary bg-secondary-container/20 px-2.5 py-1 rounded-full font-bold">3.4 KM Lagi</span>
            </div>

            <!-- Vector SVG Map Visual -->
            <div class="relative w-full h-64 lg:h-72 rounded-2xl overflow-hidden bg-[#eef3f8] flex items-end p-3.5 shadow-inner border border-slate-200">
              <!-- Styled Map Background SVG -->
              <svg class="w-full h-full absolute inset-0" preserveAspectRatio="xMidYMid slice" viewBox="0 0 600 320" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <pattern id="grid-home" width="30" height="30" patternUnits="userSpaceOnUse">
                    <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#e2e8f0" stroke-width="0.8" />
                  </pattern>
                </defs>
                <rect width="100%" height="100%" fill="#f1f5f9" />
                <rect width="100%" height="100%" fill="url(#grid-home)" />

                <!-- Coastal / River lines -->
                <path d="M-20,40 Q180,65 320,35 T620,45" fill="none" stroke="#cbd5e1" stroke-width="18" stroke-linecap="round" opacity="0.6" />
                <text x="60" y="32" fill="#64748b" font-size="9" font-weight="700">Teluk Jakarta / Kawasan Pesisir Utara</text>

                <!-- City blocks -->
                <rect x="50" y="80" width="120" height="50" rx="8" fill="#e2e8f0" opacity="0.6" />
                <text x="60" y="98" fill="#64748b" font-size="8.5" font-weight="700">BATAVIA PIK</text>

                <rect x="220" y="90" width="140" height="60" rx="8" fill="#e2e8f0" opacity="0.6" />
                <text x="235" y="110" fill="#64748b" font-size="8.5" font-weight="700">SUNTER MALL AREA</text>

                <rect x="420" y="70" width="130" height="55" rx="8" fill="#e2e8f0" opacity="0.6" />
                <text x="435" y="88" fill="#64748b" font-size="8.5" font-weight="700">RSUD KOJA MEDICAL</text>

                <!-- Highways & Arterial Roads -->
                <path d="M0,230 L160,205 L310,150 L480,120 L600,110" fill="none" stroke="#94a3b8" stroke-width="10" stroke-linecap="round" />
                <path d="M0,230 L160,205 L310,150 L480,120 L600,110" fill="none" stroke="#ffffff" stroke-width="6" stroke-linecap="round" />

                <path d="M310,0 L310,150 L340,320" fill="none" stroke="#cbd5e1" stroke-width="8" stroke-linecap="round" />
                <path d="M310,0 L310,150 L340,320" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round" />

                <!-- Route line highlighted -->
                <path d="M160,205 L240,175 L310,150" fill="none" stroke="#b10045" stroke-width="4" stroke-dasharray="6 4" />

                <!-- Destination Hospital Pin -->
                <g transform="translate(480, 120)">
                  <circle cx="0" cy="0" r="14" fill="#006c4a" fill-opacity="0.2" />
                  <circle cx="0" cy="0" r="8" fill="#006c4a" />
                  <text x="12" y="4" fill="#006c4a" font-size="9" font-weight="800">RS MEDIKA SEHAT (TUJUAN)</text>
                </g>

                <!-- Active Satria Reefer Marker -->
                <g transform="translate(310, 150)">
                  <circle cx="0" cy="0" r="28" fill="#b10045" fill-opacity="0.2" class="animate-ping" />
                  <circle cx="0" cy="0" r="14" fill="#b10045" fill-opacity="0.3" />
                  <circle cx="0" cy="0" r="7" fill="#b10045" stroke="#ffffff" stroke-width="2" />
                </g>
              </svg>

              <!-- Floating In-Transit Live HUD Card -->
              <div class="relative z-10 w-full bg-surface-container-lowest/95 backdrop-blur-md p-3 rounded-xl shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-surface-container-low flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-[22px]">vaccines</span>
                  </div>
                  <div class="flex flex-col text-left">
                    <span class="font-label-md text-xs sm:text-sm text-on-surface font-bold">Batch #VAK-2024-098</span>
                    <span class="font-body-sm text-[11px] sm:text-xs text-on-surface-variant">Jl. Gatot Subroto → RS Medika Sehat Farmasi</span>
                  </div>
                </div>

                <div class="flex items-center gap-4 shrink-0 self-end sm:self-center">
                  <div class="text-right">
                    <div class="font-label-sm text-[10px] text-on-surface-variant uppercase font-bold">Suhu Muatan</div>
                    <div class="font-headline-sm text-sm sm:text-base text-secondary font-bold flex items-center justify-end gap-1">
                      <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                      4.1°C
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="openMonitor(1, '#100098421088712')"
                    class="px-3.5 py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-on-surface font-bold text-xs transition-colors cursor-pointer"
                  >
                    Detail
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Facility Cold Storage Readiness Card (5 cols) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-surface-container-lowest to-surface-container-low rounded-3xl p-6 md:p-7 shadow-sm flex flex-col justify-between border border-slate-100">
          <div class="flex flex-col justify-between h-full space-y-4">
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs uppercase tracking-wider text-secondary font-bold flex items-center gap-1">
                  <span class="material-symbols-outlined text-[18px]">check_circle</span>
                  Kesiapan Penerimaan
                </span>
                <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold">
                  Cold-Room 2–8°C
                </span>
              </div>
              <h3 class="font-headline-md text-lg font-bold text-on-surface mb-1">Protokol Serah Terima</h3>
              <p class="font-body-sm text-xs text-on-surface-variant mb-4">3 langkah wajib sebelum membuka segel tamper-evident kargo:</p>

              <div class="flex flex-col gap-2.5">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-container-lowest shadow-xs border border-secondary/20">
                  <div class="w-8 h-8 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0 font-bold text-sm">
                    1
                  </div>
                  <div class="flex flex-col">
                    <span class="font-label-md text-xs font-bold text-on-surface">Validasi Data Logger</span>
                    <span class="text-[11px] text-on-surface-variant">Cetak &amp; simpan grafik suhu dari kurir</span>
                  </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-container-lowest shadow-xs border border-secondary/20">
                  <div class="w-8 h-8 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0 font-bold text-sm">
                    2
                  </div>
                  <div class="flex flex-col">
                    <span class="font-label-md text-xs font-bold text-on-surface">Verifikasi Segel Fisik</span>
                    <span class="text-[11px] text-on-surface-variant">Cocokkan nomor barcode dengan digital manifest</span>
                  </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-container-lowest shadow-xs border border-secondary/20">
                  <div class="w-8 h-8 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0 font-bold text-sm">
                    3
                  </div>
                  <div class="flex flex-col">
                    <span class="font-label-md text-xs font-bold text-on-surface">Tanda Tangan APJ</span>
                    <span class="text-[11px] text-on-surface-variant">Tanda tangan e-POD di terminal kurir</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-3 flex items-center justify-between border-t border-secondary/10">
              <button
                type="button"
                @click="openMonitor(1)"
                class="text-xs text-primary hover:underline font-bold flex items-center gap-1 cursor-pointer"
              >
                <span>SOP Lengkap CDOB</span>
                <span class="material-symbols-outlined text-[14px]">arrow_outward</span>
              </button>
              <span class="text-[11px] text-outline font-medium">CDOB-2024.3</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. Akses Cepat Layanan Faskes (4 Clean Minimalist Action Tiles) -->
    <section class="w-full space-y-4">
      <div class="flex flex-col gap-1">
        <h2 class="font-headline-md text-lg sm:text-xl font-bold text-on-surface">Akses Cepat Layanan Faskes</h2>
        <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant">Navigasi langsung untuk operasional logistik medis harian</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tile 1: Semua Paket -->
        <router-link
          to="/paket-saya"
          class="group flex flex-col justify-between p-5 bg-gradient-to-b from-primary-fixed/20 to-surface-container-lowest rounded-2xl border border-primary/20 shadow-sm hover:shadow-md hover:border-primary transition-all cursor-pointer"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-xl bg-primary text-on-primary flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
              <span class="material-symbols-outlined text-[24px]">inventory_2</span>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-primary group-hover:translate-x-1 transition-all">
              arrow_forward
            </span>
          </div>
          <div>
            <h3 class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Semua Paket</h3>
            <span class="text-xs text-on-surface-variant">Manifest &amp; Live Sensor</span>
          </div>
        </router-link>

        <!-- Tile 2: Pick-Up Khusus -->
        <button
          type="button"
          @click="handlePickUpSpecial"
          class="group flex flex-col justify-between p-5 bg-gradient-to-b from-secondary-container/25 to-surface-container-lowest rounded-2xl border border-secondary/20 shadow-sm hover:shadow-md hover:border-secondary transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-xl bg-secondary text-on-secondary flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
              <span class="material-symbols-outlined text-[24px]">rv_hookup</span>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-secondary group-hover:translate-x-1 transition-all">
              arrow_forward
            </span>
          </div>
          <div>
            <h3 class="text-sm font-bold text-on-surface group-hover:text-secondary transition-colors">Pick-Up Khusus</h3>
            <span class="text-xs text-on-surface-variant">Reefer Box 2–8°C</span>
          </div>
        </button>

        <!-- Tile 3: Laporan Audit -->
        <button
          type="button"
          @click="openMonitor(3)"
          class="group flex flex-col justify-between p-5 bg-gradient-to-b from-surface-container-high to-surface-container-lowest rounded-2xl border border-surface-variant shadow-sm hover:shadow-md hover:border-primary transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-xl bg-surface-container-highest text-on-surface flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
              <span class="material-symbols-outlined text-[24px]">description</span>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-primary group-hover:translate-x-1 transition-all">
              arrow_forward
            </span>
          </div>
          <div>
            <h3 class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Laporan Audit</h3>
            <span class="text-xs text-on-surface-variant">Arsip Suhu BPOM</span>
          </div>
        </button>

        <!-- Tile 4: Hotline 24 Jam -->
        <button
          type="button"
          @click="handleContactHotline"
          class="group flex flex-col justify-between p-5 bg-gradient-to-b from-primary-container/15 to-surface-container-lowest rounded-2xl border border-primary-container/20 shadow-sm hover:shadow-md hover:border-primary transition-all cursor-pointer text-left"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
              <span class="material-symbols-outlined text-[24px]">support_agent</span>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-primary group-hover:translate-x-1 transition-all">
              arrow_forward
            </span>
          </div>
          <div>
            <h3 class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Hotline 24 Jam</h3>
            <span class="text-xs text-on-surface-variant">Konsultasi Apoteker</span>
          </div>
        </button>
      </div>
    </section>

    <!-- 5. Banner Edukasi & Standar Keamanan Rantai Dingin -->
    <section class="w-full">
      <div class="rounded-2xl bg-gradient-to-r from-secondary-container/20 via-surface-container-lowest to-primary-fixed/20 p-5 sm:p-6 border border-secondary/20 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
          <div class="shrink-0 w-12 h-12 rounded-xl bg-secondary text-on-secondary flex items-center justify-center shadow-xs">
            <span class="material-symbols-outlined text-[28px]">shield_with_heart</span>
          </div>
          <div class="flex flex-col">
            <div class="flex items-center gap-2">
              <span class="font-headline-sm text-sm sm:text-base text-on-surface font-bold">Standar Keamanan Rantai Dingin</span>
              <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold">ISO 17025</span>
            </div>
            <p class="font-body-sm text-xs text-on-surface-variant font-medium">Kompresor ganda &amp; Cold Contingency Response tervalidasi BPOM RI.</p>
          </div>
        </div>

        <div class="shrink-0 w-full sm:w-auto">
          <button
            type="button"
            @click="openMonitor(3)"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-lowest hover:bg-secondary-container text-on-surface font-bold text-xs border border-secondary/20 transition-colors shadow-xs cursor-pointer"
          >
            <span>Dokumen Validasi</span>
            <span class="material-symbols-outlined text-[16px] text-secondary">verified</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Interactive Toast Feedback -->
    <NotificationToast ref="toastRef" />
  </div>
</template>

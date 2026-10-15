<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import ApiState from '../../components/layout/ApiState.vue'
import { currentUser } from '../../services/auth'
import { dateText, type LiveShipment, type Response } from '../../services/models'
import { errorText, query, send, type Page } from '../../services/api'
import { useApi, usePolling } from '../../services/useApi'

interface TelegramStatus {
  connected: boolean
}

interface TelegramLink {
  url: string
  expires_at: string
}

interface Dashboard {
  facility: { name?: string; department?: string; facility_id?: string } | null
  metrics: {
    active_shipments_count: number
    critical_count: number
    average_temperature_str: string
    compliance_rate: number | null
    measured_shipments_count: number
    simulated_measurements_count: number
    compliance_scope: string
    incident_count: number
    available_reports_count: number
    digital_hash: string | null
    next_arrival_time: string | null
    next_arrival_item: string | null
  }
  priority_shipment: LiveShipment | null
  counts: Record<string, number>
  refreshed_at: string
}

const router = useRouter()
const searchInput = ref('')
const dashboard = useApi<Response<Dashboard>>(() => '/vendor/dashboard')
const recent = useApi<Page<LiveShipment>>(() =>
  '/vendor/packages' + query({ per_page: 5, sort: 'newest' }),
)
const telegramStatus = useApi<Response<TelegramStatus>>(() => '/vendor/telegram')
const telegramLink = ref('')
const telegramLinkError = ref('')
const telegramLinkLoading = ref(false)

usePolling(
  async () => {
    await Promise.all([dashboard.reload(), recent.reload()])
  },
  () => true,
  60000,
)

usePolling(
  async () => {
    await telegramStatus.reload()
    if (telegramStatus.data.value?.data.connected) telegramLink.value = ''
  },
  () => Boolean(telegramLink.value) && !telegramStatus.data.value?.data.connected,
  5000,
)

const facilityName = computed(() => {
  const name = dashboard.data.value?.data.facility?.name
  return name || currentUser.value?.name || 'Fasilitas penerima'
})
const metrics = computed(() => dashboard.data.value?.data.metrics)

async function createTelegramLink() {
  telegramLinkLoading.value = true
  telegramLinkError.value = ''
  try {
    const response = await send<Response<TelegramLink>>('/vendor/telegram/link', {})
    telegramLink.value = response.data.url
  } catch (error) {
    telegramLinkError.value = errorText(error)
  } finally {
    telegramLinkLoading.value = false
  }
}

function searchPackages() {
  void router.push({
    name: 'vendor-packages',
    query: searchInput.value.trim() ? { search: searchInput.value.trim() } : {},
  })
}

function openShipment(shipment: LiveShipment) {
  void router.push({
    name: 'vendor-monitor',
    params: { awb: shipment.tracking_number },
    query: { screen: '1' },
  })
}

function statusTone(category: string) {
  if (category === 'transit') return 'bg-sky-50 text-sky-700 ring-sky-200'
  if (category === 'delivered') return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  return 'bg-amber-50 text-amber-800 ring-amber-200'
}

function temperatureLabel(shipment: LiveShipment) {
  if (shipment.current_temperature === null) return 'Belum ada data sensor'
  return `${shipment.current_temperature}°C`
}
</script>

<template>
  <div class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
    <section class="relative isolate overflow-hidden rounded-3xl bg-slate-950 px-6 py-8 text-white shadow-xl sm:px-9 sm:py-10">
      <div class="absolute -right-20 -top-24 -z-10 h-72 w-72 rounded-full bg-primary/50 blur-3xl" />
      <div class="absolute -bottom-32 right-1/3 -z-10 h-64 w-64 rounded-full bg-emerald-500/25 blur-3xl" />
      <div class="grid items-end gap-8 lg:grid-cols-[1.3fr_0.7fr]">
        <div>
          <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-100">
            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400" />
            Portal penerima · pemantauan cold-chain
          </p>
          <p class="mt-6 text-sm font-medium text-slate-300">Selamat datang kembali</p>
          <h1 class="mt-1 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
            {{ facilityName }}
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
            Pantau perjalanan, kondisi suhu, dan dokumen serah terima kiriman farmasi Anda dari satu tempat.
          </p>
        </div>
        <form class="rounded-2xl border border-white/10 bg-white/10 p-2 backdrop-blur" @submit.prevent="searchPackages">
          <label for="shipment-search" class="sr-only">Cari nomor resi atau nama produk</label>
          <div class="flex gap-2">
            <input
              id="shipment-search"
              v-model="searchInput"
              type="search"
              autocomplete="off"
              placeholder="Cari resi atau nama produk"
              class="min-w-0 flex-1 rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-white outline-none placeholder:text-slate-400 focus:border-emerald-300 focus:ring-2 focus:ring-emerald-300/20"
            />
            <button type="submit" class="rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white transition hover:bg-rose-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
              Cari
            </button>
          </div>
          <p class="px-2 pt-2 text-xs text-slate-300">Pencarian diproses langsung oleh data kiriman akun Anda.</p>
        </form>
      </div>
    </section>

    <section class="live-panel flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Notifikasi pengiriman</p>
        <h2 class="mt-1 text-lg font-bold text-slate-900">Hubungkan Telegram</h2>
        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
          Hubungkan akun Telegram agar notifikasi status dan laporan kiriman dapat dikirim langsung ke chat Anda. Nomor telepon tidak diperlukan.
        </p>
        <p v-if="telegramStatus.data.value?.data.connected" class="mt-2 text-sm font-semibold text-emerald-700" role="status">
          Telegram sudah terhubung ke akun ini.
        </p>
        <p v-else class="mt-2 text-sm text-amber-700">
          Telegram belum terhubung. Tautan berlaku 15 menit dan hanya dapat digunakan sekali.
        </p>
        <p v-if="telegramStatus.error.value" class="mt-2 text-sm font-semibold text-rose-700" role="alert">
          Status Telegram belum dapat diperiksa. {{ telegramStatus.error.value }}
        </p>
        <p v-if="telegramLinkError" class="mt-2 text-sm font-semibold text-rose-700" role="alert">
          {{ telegramLinkError }}
        </p>
      </div>
      <div class="flex shrink-0 flex-wrap gap-2">
        <button
          v-if="!telegramStatus.data.value?.data.connected"
          type="button"
          class="live-button live-primary"
          :disabled="telegramLinkLoading"
          @click="createTelegramLink"
        >
          {{ telegramLinkLoading ? 'Menyiapkan tautan…' : 'Buat tautan Telegram' }}
        </button>
        <a
          v-if="telegramLink && !telegramStatus.data.value?.data.connected"
          :href="telegramLink"
          target="_blank"
          rel="noopener noreferrer"
          class="live-button"
        >
          Buka bot dan tekan Start
        </a>
      </div>
    </section>

    <ApiState :loading="dashboard.loading.value" :error="dashboard.error.value" @retry="dashboard.reload" />

    <template v-if="metrics">
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan pengiriman">
        <article class="live-panel !p-5">
          <p class="text-sm font-medium text-slate-500">Kiriman aktif</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ metrics.active_shipments_count }}</p>
          <RouterLink to="/paket-saya?category=all" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline">
            Lihat semua kiriman <span aria-hidden="true">→</span>
          </RouterLink>
        </article>
        <article class="live-panel !p-5">
          <p class="text-sm font-medium text-slate-500">Perlu perhatian</p>
          <p class="mt-3 text-3xl font-bold tracking-tight" :class="metrics.critical_count ? 'text-rose-700' : 'text-slate-900'">
            {{ metrics.critical_count }}
          </p>
          <p class="mt-3 text-sm text-slate-500">{{ metrics.incident_count }} kendala terbuka</p>
        </article>
        <article class="live-panel !p-5">
          <p class="text-sm font-medium text-slate-500">Suhu rata-rata terkini</p>
          <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ metrics.average_temperature_str }}</p>
          <p class="mt-3 text-sm text-slate-500">Berdasarkan sensor yang masih segar</p>
        </article>
        <article class="live-panel !p-5">
          <p class="text-sm font-medium text-slate-500">Dokumen tersedia</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ metrics.available_reports_count }}</p>
          <p class="mt-3 text-sm text-slate-500">Laporan untuk kiriman akun ini</p>
        </article>
      </section>

      <section class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
        <div class="live-panel !p-0">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Prioritas pemantauan</p>
              <h2 class="mt-1 text-lg font-bold text-slate-900">Kiriman yang perlu Anda lihat</h2>
            </div>
            <button type="button" class="live-button" :disabled="dashboard.loading.value" @click="dashboard.reload">
              Muat ulang
            </button>
          </div>
          <div v-if="dashboard.data.value?.data.priority_shipment" class="p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div class="min-w-0">
                <p class="font-mono text-sm font-bold text-primary">{{ dashboard.data.value.data.priority_shipment.tracking_number }}</p>
                <h3 class="mt-2 text-xl font-bold text-slate-900">{{ dashboard.data.value.data.priority_shipment.item_summary }}</h3>
                <p class="mt-2 text-sm text-slate-500">{{ dashboard.data.value.data.priority_shipment.route_display }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset" :class="statusTone(dashboard.data.value.data.priority_shipment.category)">
                {{ dashboard.data.value.data.priority_shipment.status_label }}
              </span>
            </div>
            <div class="mt-6 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Suhu terakhir</p>
                <p class="mt-1 font-bold text-slate-900">{{ temperatureLabel(dashboard.data.value.data.priority_shipment) }}</p>
                <p class="mt-1 text-xs" :class="dashboard.data.value.data.priority_shipment.telemetry_fresh ? 'text-emerald-700' : 'text-amber-700'">
                  {{ dashboard.data.value.data.priority_shipment.telemetry_fresh ? 'Data sensor terkini' : dashboard.data.value.data.priority_shipment.temp_label }}
                </p>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Perkiraan / jadwal</p>
                <p class="mt-1 font-bold text-slate-900">{{ dashboard.data.value.data.priority_shipment.estimate_label }}</p>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Penerimaan</p>
                <p class="mt-1 font-bold text-slate-900">{{ dashboard.data.value.data.priority_shipment.recipient_ready === null ? 'Belum diminta' : dashboard.data.value.data.priority_shipment.recipient_ready ? 'Siap menerima' : 'Belum dikonfirmasi' }}</p>
              </div>
            </div>
            <button type="button" class="live-button live-primary mt-5" @click="openShipment(dashboard.data.value.data.priority_shipment)">
              Buka pemantauan kiriman <span aria-hidden="true">→</span>
            </button>
          </div>
          <div v-else class="px-5 py-10 text-center sm:px-6">
            <p class="font-semibold text-slate-900">Belum ada kiriman aktif</p>
            <p class="mt-2 text-sm text-slate-500">Kiriman baru akan muncul di sini setelah terdaftar untuk fasilitas Anda.</p>
          </div>
        </div>

        <aside class="live-panel flex flex-col justify-between !p-6">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Integritas data</p>
            <h2 class="mt-2 text-lg font-bold text-slate-900">Kepatuhan sensor terkini</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
              Persentase ini hanya merangkum sampel terbaru yang tersedia, bukan sertifikasi mutu pengiriman.
            </p>
            <p class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900">
              {{ metrics.compliance_rate === null ? '—' : `${metrics.compliance_rate}%` }}
            </p>
            <p class="mt-2 text-sm text-slate-500">{{ metrics.measured_shipments_count }} kiriman memiliki data sensor segar</p>
            <p v-if="metrics.simulated_measurements_count > 0" class="mt-2 text-xs font-semibold text-amber-700">
              Termasuk {{ metrics.simulated_measurements_count }} sampel simulasi
            </p>
          </div>
          <div class="mt-6 border-t border-slate-100 pt-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pembaruan data</p>
            <p class="mt-1 text-sm font-medium text-slate-700">{{ dateText(dashboard.data.value?.data.refreshed_at) }}</p>
          </div>
        </aside>
      </section>

      <section class="live-panel !p-0">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Aktivitas terbaru</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Kiriman Anda</h2>
          </div>
          <RouterLink to="/paket-saya" class="text-sm font-bold text-primary hover:underline">Buka daftar kiriman →</RouterLink>
        </div>
        <ApiState :loading="recent.loading.value" :error="recent.error.value" @retry="recent.reload" />
        <div v-if="recent.data.value?.data.length" class="divide-y divide-slate-100">
          <button
            v-for="shipment in recent.data.value.data"
            :key="shipment.id"
            type="button"
            class="flex w-full flex-wrap items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50 sm:px-6"
            @click="openShipment(shipment)"
          >
            <span class="min-w-0 flex-1">
              <span class="block font-mono text-xs font-bold text-primary">{{ shipment.tracking_number }}</span>
              <span class="mt-1 block truncate text-sm font-semibold text-slate-900">{{ shipment.item_summary }}</span>
              <span class="mt-1 block truncate text-xs text-slate-500">{{ shipment.route_display }}</span>
            </span>
            <span class="flex items-center gap-3">
              <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset" :class="statusTone(shipment.category)">{{ shipment.status_label }}</span>
              <span aria-hidden="true" class="text-slate-400">→</span>
            </span>
          </button>
        </div>
        <p v-else-if="!recent.loading.value && !recent.error.value" class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">
          Belum ada kiriman yang terdaftar untuk akun Anda.
        </p>
      </section>
    </template>
  </div>
</template>

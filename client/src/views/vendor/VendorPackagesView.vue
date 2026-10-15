<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ApiState from '../../components/layout/ApiState.vue'
import { download, errorText, query, type Page } from '../../services/api'
import { dateText, type LiveShipment } from '../../services/models'
import { useApi } from '../../services/useApi'

type Category = 'all' | 'transit' | 'qc' | 'delivered'
const route = useRoute()
const router = useRouter()
const categoryOptions: { value: Category; label: string }[] = [
  { value: 'all', label: 'Semua kiriman' },
  { value: 'transit', label: 'Dalam perjalanan' },
  { value: 'qc', label: 'Pemeriksaan & persiapan' },
  { value: 'delivered', label: 'Selesai / diterima' },
]
const category = computed<Category>(() => {
  const value = route.query.category
  return categoryOptions.some((option) => option.value === value)
    ? (value as Category)
    : 'all'
})
const page = computed(() => {
  const value = Number(route.query.page)
  return Number.isInteger(value) && value > 0 ? value : 1
})
const searchInput = ref(typeof route.query.search === 'string' ? route.query.search : '')
const exporting = ref(false)
const exportError = ref('')

watch(
  () => route.query.search,
  (value) => {
    searchInput.value = typeof value === 'string' ? value : ''
  },
)

const requestPath = computed(
  () =>
    '/vendor/packages' +
    query({
      category: category.value,
      search: typeof route.query.search === 'string' ? route.query.search : undefined,
      page: page.value,
      per_page: 10,
      sort: 'newest',
    }),
)
const packages = useApi<Page<LiveShipment>>(requestPath)

function updateQuery(values: { category?: Category; search?: string; page?: number }) {
  void router.replace({
    query: {
      ...route.query,
      category: values.category === 'all' ? undefined : values.category,
      search: values.search || undefined,
      page: values.page && values.page > 1 ? String(values.page) : undefined,
    },
  })
}

function search() {
  updateQuery({ category: category.value, search: searchInput.value.trim() })
}

function selectCategory(value: Category) {
  updateQuery({ category: value, search: typeof route.query.search === 'string' ? route.query.search : '' })
}

function openShipment(shipment: LiveShipment) {
  void router.push({
    name: 'vendor-monitor',
    params: { awb: shipment.tracking_number },
    query: { screen: '1' },
  })
}

function statusTone(categoryValue: string) {
  if (categoryValue === 'transit') return 'bg-sky-50 text-sky-700 ring-sky-200'
  if (categoryValue === 'delivered') return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  return 'bg-amber-50 text-amber-800 ring-amber-200'
}

function temperatureLabel(shipment: LiveShipment) {
  if (shipment.current_temperature === null) return 'Tidak tersedia'
  return `${shipment.current_temperature}°C`
}

async function exportPackages() {
  if (exporting.value) return
  exporting.value = true
  exportError.value = ''
  try {
    await download(
      '/vendor/packages/export' +
        query({
          category: category.value,
          search: typeof route.query.search === 'string' ? route.query.search : undefined,
        }),
      'rekap-paket-farmasi.pdf',
    )
  } catch (error) {
    exportError.value = errorText(error)
  } finally {
    exporting.value = false
  }
}
</script>

<template>
  <div class="mx-auto flex w-full max-w-7xl flex-col gap-7 px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Ruang penerima</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Paket dan laporan saya</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
          Cari kiriman milik akun Anda, tinjau tahap penanganan, lalu buka telemetri atau dokumen serah terima.
        </p>
      </div>
      <button type="button" class="live-button" :disabled="exporting" @click="exportPackages">
        <span aria-hidden="true">{{ exporting ? '…' : '↓' }}</span>
        {{ exporting ? 'Menyiapkan PDF…' : 'Unduh rekap PDF' }}
      </button>
    </header>

    <p v-if="exportError" role="alert" class="live-feedback bg-error-container text-on-error-container">
      {{ exportError }}
    </p>

    <section class="live-panel !p-4 sm:!p-5" aria-label="Cari dan filter paket">
      <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="search">
        <label for="package-search" class="sr-only">Cari nomor resi, produk, atau kota</label>
        <input
          id="package-search"
          v-model="searchInput"
          type="search"
          autocomplete="off"
          placeholder="Cari nomor resi, produk, atau kota"
          class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/10"
        />
        <button type="submit" class="live-button live-primary">Cari kiriman</button>
      </form>
      <div class="mt-4 flex gap-2 overflow-x-auto pb-1" aria-label="Filter kategori">
        <button
          v-for="option in categoryOptions"
          :key="option.value"
          type="button"
          class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
          :class="category === option.value ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          :aria-pressed="category === option.value"
          @click="selectCategory(option.value)"
        >
          {{ option.label }}
          <span v-if="packages.data.value?.counts?.[option.value] !== undefined" class="ml-1 opacity-70">
            {{ packages.data.value.counts[option.value] }}
          </span>
        </button>
      </div>
    </section>

    <ApiState :loading="packages.loading.value" :error="packages.error.value" @retry="packages.reload" />

    <section v-if="packages.data.value" class="flex flex-col gap-4" aria-live="polite">
      <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
        <p>
          <span class="font-bold text-slate-900">{{ packages.data.value.meta.total }}</span>
          kiriman ditemukan
          <span v-if="typeof route.query.search === 'string'">untuk “{{ route.query.search }}”</span>
        </p>
        <p>Halaman {{ packages.data.value.meta.current_page }} dari {{ packages.data.value.meta.last_page }}</p>
      </div>

      <div v-if="packages.data.value.data.length" class="grid gap-4 lg:grid-cols-2">
        <article
          v-for="shipment in packages.data.value.data"
          :key="shipment.id"
          class="live-panel flex min-w-0 flex-col !p-5 transition hover:border-primary/30 hover:shadow-md sm:!p-6"
        >
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-mono text-sm font-bold text-primary">{{ shipment.tracking_number }}</p>
              <h2 class="mt-2 break-words text-lg font-bold text-slate-900">{{ shipment.item_summary }}</h2>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset" :class="statusTone(shipment.category)">
              {{ shipment.status_label }}
            </span>
          </div>

          <div class="mt-5 rounded-xl bg-slate-50 p-4">
            <div class="flex items-center gap-3 text-sm">
              <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-slate-500 ring-1 ring-slate-200" aria-hidden="true">↗</span>
              <p class="min-w-0 break-words font-medium text-slate-700">{{ shipment.route_display }}</p>
            </div>
            <div class="ml-4 mt-2 h-4 border-l border-dashed border-slate-300" />
            <div class="flex items-center gap-3 text-sm">
              <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200" aria-hidden="true">⌖</span>
              <p class="min-w-0 break-words text-slate-700">{{ shipment.last_location || shipment.destination_city }}</p>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-2 gap-4 border-b border-slate-100 pb-5">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Suhu terakhir</p>
              <p class="mt-1 font-bold text-slate-900">{{ temperatureLabel(shipment) }}</p>
              <p class="mt-1 text-xs" :class="shipment.telemetry_fresh ? 'text-emerald-700' : 'text-amber-700'">
                {{ shipment.telemetry_fresh ? 'Sensor terkini' : shipment.temp_label }}
              </p>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jadwal</p>
              <p class="mt-1 text-sm font-bold text-slate-900">{{ dateText(shipment.scheduled_at) }}</p>
            </div>
          </div>

          <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-4">
            <p v-if="shipment.is_demo" class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">Data simulasi</p>
            <p v-else class="text-xs text-slate-500">{{ shipment.package_count }} paket · {{ shipment.weight_kg }} kg</p>
            <button type="button" class="live-button live-primary ml-auto" @click="openShipment(shipment)">
              Pantau kiriman <span aria-hidden="true">→</span>
            </button>
          </div>
        </article>
      </div>
      <div v-else-if="!packages.loading.value" class="live-panel px-6 py-12 text-center">
        <p class="text-lg font-bold text-slate-900">Tidak ada kiriman yang cocok</p>
        <p class="mt-2 text-sm text-slate-500">Ubah kata pencarian atau pilih kategori lain.</p>
        <button type="button" class="live-button mt-5" @click="searchInput = ''; updateQuery({ category: 'all', search: '' })">
          Hapus filter
        </button>
      </div>

      <nav v-if="packages.data.value.meta.last_page > 1" class="flex items-center justify-center gap-3" aria-label="Navigasi halaman kiriman">
        <button type="button" class="live-button" :disabled="page <= 1 || packages.loading.value" @click="updateQuery({ category: category, search: typeof route.query.search === 'string' ? route.query.search : '', page: page - 1 })">
          Sebelumnya
        </button>
        <span class="text-sm font-semibold text-slate-600">{{ page }} / {{ packages.data.value.meta.last_page }}</span>
        <button type="button" class="live-button" :disabled="page >= packages.data.value.meta.last_page || packages.loading.value" @click="updateQuery({ category: category, search: typeof route.query.search === 'string' ? route.query.search : '', page: page + 1 })">
          Berikutnya
        </button>
      </nav>
    </section>
  </div>
</template>

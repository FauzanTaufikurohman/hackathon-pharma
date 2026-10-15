<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ApiState from '../../components/layout/ApiState.vue'
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'
import AdminLiveManifest from '../../components/admin/AdminLiveManifest.vue'
import AdminLiveShipment from '../../components/admin/AdminLiveShipment.vue'
import { useApi, usePolling } from '../../services/useApi'
import type { Response } from '../../services/models'

interface AdminMetrics {
  total: number
  statuses: Record<string, number>
  open_issues: number
  available_reports: number
  updated_at: string
}

const route = useRoute()
const router = useRouter()
const refresh = ref(0)
const manifestOpen = ref(false)
const selected = computed(() =>
  typeof route.query.awb === 'string' ? route.query.awb : '',
)
const metrics = useApi<Response<AdminMetrics>>(() => '/admin/metrics')

usePolling(metrics.reload, () => true, 30000)

const cards = computed(() => {
  const values = metrics.data.value?.data
  return [
    {
      label: 'Total kiriman',
      value: values?.total,
      detail: 'Semua status operasional',
      tone: 'text-slate-900',
    },
    {
      label: 'Menunggu persetujuan',
      value: values
        ? (values.statuses.pending_approval ?? 0)
        : undefined,
      detail: 'Pemeriksaan Checker selesai',
      tone: 'text-amber-700',
    },
    {
      label: 'Dalam perjalanan',
      value: values
        ? (values.statuses.in_transit ?? 0)
        : undefined,
      detail: 'Kiriman sedang menuju penerima',
      tone: 'text-sky-700',
    },
    {
      label: 'Kendala terbuka',
      value: values?.open_issues,
      detail: 'Perlu ditinjau oleh Admin',
      tone: 'text-rose-700',
    },
  ]
})

function select(awb: string) {
  void router.replace({ query: { ...route.query, awb } })
}

function clearSelection() {
  const query = { ...route.query }
  delete query.awb
  void router.replace({ query })
}

function created(awb: string) {
  refresh.value++
  manifestOpen.value = false
  select(awb)
  void metrics.reload()
}

function changed() {
  refresh.value++
  void metrics.reload()
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-7xl flex-col gap-7 px-4 py-7 sm:px-6 lg:px-8">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">
          Operasional · Admin
        </p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
          Manajemen pengiriman
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
          Pantau antrean dan tahap kiriman, lalu buka satu resi untuk melihat detail atau mengambil tindakan berikutnya.
        </p>
      </div>
      <RouterLink
        to="/admin/approval-qc-kurir"
        class="live-button self-start sm:self-auto"
      >
        Buka persetujuan <span aria-hidden="true">→</span>
      </RouterLink>
    </header>

    <section aria-label="Ringkasan operasional" class="flex flex-col gap-3">
      <ApiState
        :loading="metrics.loading.value"
        :error="metrics.error.value"
        @retry="metrics.reload"
      />
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article
          v-for="card in cards"
          :key="card.label"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          <p class="text-sm font-semibold text-slate-500">{{ card.label }}</p>
          <p class="mt-3 text-3xl font-extrabold tracking-tight" :class="card.tone">
            {{ card.value ?? '—' }}
          </p>
          <p class="mt-2 text-xs text-slate-500">{{ card.detail }}</p>
        </article>
      </div>
    </section>

    <section class="grid min-w-0 items-start gap-6 xl:grid-cols-12" aria-label="Alur kerja pengiriman">
      <div class="min-w-0 xl:col-span-5">
        <div class="mb-3">
          <h2 class="text-lg font-bold text-slate-900">Daftar kiriman</h2>
          <p class="mt-1 text-sm text-slate-500">Cari atau filter resi untuk membuka detail.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
          <ShipmentDirectory
            role="admin"
            selectable
            :refresh-key="refresh"
            @select="select"
          />
        </div>
      </div>

      <div class="min-w-0 xl:col-span-7">
        <div class="mb-3">
          <h2 class="text-lg font-bold text-slate-900">Detail dan tindakan</h2>
          <p class="mt-1 text-sm text-slate-500">Pilih kiriman dari daftar untuk melihat langkah yang tersedia.</p>
        </div>
        <AdminLiveShipment
          v-if="selected"
          :key="selected"
          :awb="selected"
          mode="summary"
          @changed="changed"
        />
        <div v-else class="flex min-h-56 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
          <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-xl text-slate-500" aria-hidden="true">↗</span>
          <p class="mt-4 font-bold text-slate-900">Belum ada kiriman dipilih</p>
          <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">
            Pilih satu resi di daftar. Informasi dan tindakan akan ditampilkan di area ini.
          </p>
        </div>
      </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <button
        type="button"
        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left focus-visible:outline-2 focus-visible:outline-primary sm:px-6"
        :aria-expanded="manifestOpen"
        aria-controls="shipment-manifest-form"
        @click="manifestOpen = !manifestOpen"
      >
        <span>
          <span class="block font-bold text-slate-900">Daftarkan kiriman baru</span>
          <span class="mt-1 block text-sm text-slate-500">
            {{ manifestOpen ? 'Tutup formulir manifest.' : 'Buka formulir hanya saat perlu membuat manifest.' }}
          </span>
        </span>
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-lg text-slate-600 transition" aria-hidden="true">
          {{ manifestOpen ? '−' : '+' }}
        </span>
      </button>
      <div v-if="manifestOpen" id="shipment-manifest-form" class="border-t border-slate-100 p-4 sm:p-6">
        <AdminLiveManifest @created="created" />
      </div>
    </section>

    <button
      v-if="selected"
      type="button"
      class="self-start text-sm font-semibold text-slate-500 underline decoration-slate-300 underline-offset-4 hover:text-slate-900"
      @click="clearSelection"
    >
      Tutup detail resi
    </button>
  </main>
</template>

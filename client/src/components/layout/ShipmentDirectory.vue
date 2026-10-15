<script setup lang="ts">
import { computed, onScopeDispose, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ApiState from './ApiState.vue'
import { useApi } from '../../services/useApi'
import { download, errorText, query, type Page } from '../../services/api'
import type { LiveShipment, LiveTask } from '../../services/models'
const props = defineProps<{
  role: 'admin' | 'checker' | 'courier' | 'vendor'
  selectable?: boolean
  refreshKey?: number
}>()
const emit = defineEmits<{ select: [awb: string] }>()
const router = useRouter()
const route = useRoute()
const search = ref(''),
  debounced = ref(''),
  filter = ref('all'),
  page = ref(1),
  perPage = ref(20),
  actionError = ref('')
let timer: ReturnType<typeof setTimeout> | undefined
watch(search, (value) => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    debounced.value = value.trim()
    page.value = 1
  }, 300)
})
watch([filter, perPage], () => {
  page.value = 1
})
onScopeDispose(() => clearTimeout(timer))
const statuses = computed(() =>
  props.role === 'vendor'
    ? ['all', 'transit', 'qc', 'delivered']
    : props.role === 'courier'
      ? ['all', 'assigned', 'in_transit', 'hold', 'delivered']
      : [
          'all',
          'inspection',
          'pending_approval',
          'approved',
          'assigned',
          'accepted',
          'in_transit',
          'arrived',
          'held',
          'deviation_hold',
          'completed',
          'returned',
          'cancelled',
        ],
)
if (
  typeof route.query.status === 'string' &&
  statuses.value.includes(route.query.status)
)
  filter.value = route.query.status
const url = computed(
  () =>
    ({
      admin: '/admin/shipments',
      checker: '/checker/queue',
      courier: '/courier/tasks',
      vendor: '/vendor/packages',
    })[props.role] +
    query({
      search: debounced.value,
      page: page.value,
      per_page: perPage.value,
      ...(props.role === 'vendor'
        ? { category: filter.value }
        : props.role !== 'checker' && filter.value !== 'all'
          ? { status: filter.value }
          : {}),
    }),
)
const { data, loading, error, reload } =
  useApi<Page<LiveShipment | LiveTask>>(url)
watch(
  () => props.refreshKey,
  () => {
    void reload()
  },
)
const rows = computed(() =>
  (data.value?.data || []).map((item) =>
    'awb' in item
      ? {
          id: item.id,
          awb: item.awb,
          title: item.item_title,
          from: item.origin_name,
          to: item.destination_name,
          status: item.status_label,
          detail: item.destination_address,
          temp: item.probe_temp_str,
          stale: item.telemetry_stale,
        }
      : {
          id: item.id,
          awb: item.tracking_number,
          title: item.item_summary,
          from: item.sender_name,
          to: item.recipient_name,
          status: item.status_label,
          detail: item.route_display,
          temp:
            item.current_temperature === null
              ? 'Belum ada sampel'
              : item.current_temperature + ' °C',
          stale: !item.telemetry_fresh,
        },
  ),
)
function open(awb: string) {
  if (props.selectable) {
    emit('select', awb)
    return
  }
  void router.push(
    props.role === 'vendor'
      ? '/vendor/monitor/' + encodeURIComponent(awb)
      : props.role === 'courier'
        ? '/courier/tugas/' + encodeURIComponent(awb)
        : '/checker/form-initial-exam?awb=' + encodeURIComponent(awb),
  )
}
async function exportFile() {
  actionError.value = ''
  try {
    await download(
      props.role === 'admin'
        ? '/admin/shipments/export'
        : '/vendor/packages/export',
      props.role === 'admin' ? 'manifest-pharma.csv' : 'rekap-kiriman.pdf',
    )
  } catch (cause) {
    actionError.value = errorText(cause)
  }
}
defineExpose({ reload })
function submitSearch() {
  debounced.value = search.value.trim()
  page.value = 1
}
</script>
<template>
  <section class="flex flex-col gap-5 min-w-0">
    <form
      class="flex flex-col sm:flex-row gap-3"
      @submit.prevent="submitSearch"
    >
      <label class="live-field flex-1"
        ><span>Cari resi atau penerima</span
        ><input
          v-model="search"
          type="search"
          name="search"
          maxlength="120"
          placeholder="Nomor resi…"
      /></label>
      <label v-if="role !== 'checker'" class="live-field"
        ><span>Status kiriman</span
        ><select v-model="filter" name="status">
          <option v-for="status in statuses" :key="status" :value="status">
            {{ status === 'all' ? 'Semua' : status.replaceAll('_', ' ') }}
          </option>
        </select></label
      >
      <label class="live-field"
        ><span>Per halaman</span
        ><select v-model="perPage" name="per_page">
          <option v-for="n in [5, 20, 50, 100]" :key="n" :value="n">
            {{ n }}
          </option>
        </select></label
      >
    </form>
    <div class="flex flex-wrap justify-between items-center gap-3">
      <p>{{ data?.meta.total ?? 0 }} kiriman sesuai pencarian</p>
      <div class="flex gap-2">
        <button
          type="button"
          class="live-button"
          :disabled="loading"
          @click="reload"
        >
          Muat ulang</button
        ><button
          v-if="role === 'vendor' || role === 'admin'"
          type="button"
          class="live-button"
          @click="exportFile"
        >
          Unduh rekap
        </button>
      </div>
    </div>
    <ApiState
      :loading="loading"
      :error="error || actionError"
      @retry="reload"
    />
    <p v-if="!loading && !error && !rows.length" class="live-panel">
      Tidak ada kiriman yang sesuai. Coba ubah pencarian atau status.
    </p>
    <article
      v-for="row in rows"
      :key="row.id"
      class="live-panel flex flex-col sm:flex-row justify-between gap-5"
    >
      <div class="min-w-0">
        <p class="font-semibold text-primary break-all">{{ row.awb }}</p>
        <h2 class="text-lg font-semibold mt-1">{{ row.title }}</h2>
        <p class="mt-2">{{ row.from }} → {{ row.to }}</p>
        <p class="text-on-surface-variant mt-1">{{ row.detail }}</p>
        <p class="mt-3">
          {{ row.temp }}
          <span v-if="row.stale" class="text-tertiary"
            >· Data sensor belum diperbarui</span
          >
        </p>
      </div>
      <div class="flex sm:items-end flex-col justify-between gap-4 shrink-0">
        <p class="live-badge bg-primary/10 text-primary">{{ row.status }}</p>
        <button
          type="button"
          class="live-button"
          :aria-label="'Buka kiriman ' + row.awb"
          @click="open(row.awb)"
        >
          Buka kiriman →
        </button>
      </div>
    </article>
    <nav
      v-if="data && data.meta.last_page > 1"
      aria-label="Halaman daftar"
      class="flex items-center gap-3"
    >
      <button
        type="button"
        class="live-button"
        :disabled="page <= 1 || loading"
        @click="page--"
      >
        Sebelumnya
      </button>
      <p>{{ page }} / {{ data.meta.last_page }}</p>
      <button
        type="button"
        class="live-button"
        :disabled="page >= data.meta.last_page || loading"
        @click="page++"
      >
        Berikutnya
      </button>
    </nav>
  </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi, useMutation, usePolling } from '../../services/useApi'
import { download, errorText, query, type Page } from '../../services/api'
import {
  awbPath,
  dateText,
  type LiveShipment,
  type Response,
  type Sample,
  type Issue,
  type Report,
  type EventRecord,
} from '../../services/models'
import ApiState from '../layout/ApiState.vue'
import ShipmentRecord from '../layout/ShipmentRecord.vue'
import PrivateImage from '../layout/PrivateImage.vue'
import TelemetryRecords from '../layout/TelemetryRecords.vue'
import MonitorTabNav from '../monitor/MonitorTabNav.vue'
const props = defineProps<{ awb: string }>()
const route = useRoute(),
  router = useRouter(),
  mutation = useMutation(),
  actionError = ref('')
const tab = computed(() =>
  [1, 2, 3].includes(Number(route.query.screen))
    ? Number(route.query.screen)
    : 1,
)
const base = computed(() => '/vendor/shipments/' + awbPath(props.awb))
const issuesPage = ref(1), reportsPage = ref(1), eventsPage = ref(1)
const record = useApi<Response<LiveShipment>>(() =>
  props.awb ? base.value : '',
)
const telemetry = useApi<Page<Sample>>(() =>
  props.awb && tab.value === 2 ? base.value + '/telemetry?per_page=100' : '',
)
const issues = useApi<Page<Issue>>(() =>
  props.awb && tab.value === 2 ? base.value + '/issues' + query({ page: issuesPage.value }) : '',
)
const reports = useApi<Page<Report>>(() =>
  props.awb && tab.value === 3 ? base.value + '/reports' + query({ page: reportsPage.value }) : '',
)
const events = useApi<Page<EventRecord>>(() =>
  props.awb && tab.value === 3 ? base.value + '/events' + query({ per_page: 100, page: eventsPage.value }) : '',
)
const shipment = computed(() => record.data.value?.data)
const searchAwb = ref(props.awb)
watch(() => props.awb, (value) => {
  searchAwb.value = value
  issuesPage.value = reportsPage.value = eventsPage.value = 1
})
const pageTitle = computed(() => ['Halaman Pantau', 'Pelacakan GPS & Suhu', 'Laporan Pengiriman'][tab.value - 1])
const phaseByStatus: Record<string, number> = {
  created: 0, inspection: 1, recheck: 1, pending_approval: 2, approved: 2,
  assigned: 3, in_transit: 3, arrived: 3, completed: 4, delivered: 4, returned: 4,
}
const phase = computed(() => phaseByStatus[shipment.value?.status || ''])
const steps = ['Terdaftar', 'Pemeriksaan', 'Persetujuan', 'Pengantaran', 'Selesai']
async function search() {
  const value = searchAwb.value.trim()
  if (!value) return
  await router.push({ name: 'vendor-monitor', params: { awb: value }, query: { screen: '1' } })
  if (value === props.awb) await record.reload()
}
usePolling(reload, () => tab.value === 2 && !mutation.busy.value)
const proof = useApi<
  Response<{
    recipient_name: string
    recipient_department: string | null
    temperature: number
    seal_intact: boolean
    handed_over_at: string
    signature_url: string | null
  }>
>(() =>
  shipment.value &&
  tab.value === 3 &&
  ['completed', 'delivered', 'returned'].includes(shipment.value.status)
    ? base.value + '/proof'
    : '',
)
async function readiness() {
  if (!shipment.value) return
  if (
    await mutation.run(
      base.value + '/readiness',
      {
        version: shipment.value.version,
        recipient_ready: !shipment.value.recipient_ready,
      },
      'PUT',
    )
  )
    await record.reload()
}
async function downloadReport(report: Report) {
  actionError.value = ''
  try {
    await download(
      '/vendor/reports/' + report.id + '/download',
      'laporan-' + report.id + '.pdf',
    )
  } catch (cause) {
    actionError.value = errorText(cause)
  }
}
async function reload() {
  await Promise.all([
    record.reload(),
    telemetry.reload(),
    issues.reload(),
    reports.reload(),
    events.reload(),
    proof.reload(),
  ])
}
</script>
<template>
  <div class="flex flex-col gap-5">
    <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
      <div class="min-w-0">
        <p class="text-primary font-semibold">Pengiriman Pharma</p>
        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight mt-2">{{ pageTitle }}</h1>
        <p class="text-on-surface-variant mt-2">Status, pemeriksaan, dan bukti perjalanan untuk kirimanmu.</p>
      </div>
      <form class="live-field lg:w-96" @submit.prevent="search">
        <label for="monitor-awb" class="font-semibold">Cari nomor resi</label>
        <div class="flex gap-2">
          <input id="monitor-awb" v-model="searchAwb" name="awb" type="search" required maxlength="100" class="min-w-0 flex-1" autocomplete="off" />
          <button type="submit" class="live-button shrink-0">Lacak</button>
        </div>
      </form>
    </header>
    <MonitorTabNav
      :active-tab="tab"
      :awb-number="awb"
      @update:active-tab="
        (value) =>
          router.replace({ query: { ...route.query, screen: String(value) } })
      "
    />
    <ApiState
      :loading="record.loading.value"
      :error="record.error.value || mutation.error.value || actionError"
      :notice="mutation.notice.value"
      @retry="reload"
    />
    <template v-if="shipment">
      <section class="rounded-2xl bg-primary text-on-primary p-5 sm:p-6 flex flex-col sm:flex-row justify-between gap-4" aria-label="Status kiriman">
        <div class="min-w-0">
          <p class="opacity-80">Status saat ini</p>
          <h2 class="text-xl font-semibold mt-1">{{ shipment.status_label }}</h2>
          <p class="mt-2">{{ shipment.last_location || 'Lokasi belum dicatat' }}</p>
        </div>
        <div class="flex flex-col sm:items-end gap-2">
          <p class="rounded-full bg-white/15 px-3 py-2 tabular-nums">{{ shipment.temperature_min }}–{{ shipment.temperature_max }} °C</p>
          <p>{{ shipment.telemetry_fresh ? 'Sampel terbaru tersedia' : 'Menunggu pembaruan sampel' }}</p>
        </div>
      </section>
      <ol v-if="phase !== undefined" role="list" class="grid grid-cols-1 sm:grid-cols-5 gap-3 rounded-2xl border border-outline/15 bg-surface-container-lowest p-5" aria-label="Tahapan berdasarkan status saat ini">
        <li v-for="(step, index) in steps" :key="step" class="flex sm:flex-col items-center gap-2 text-base sm:text-sm" :aria-current="index === phase ? 'step' : undefined">
          <span class="flex size-8 shrink-0 items-center justify-center rounded-full tabular-nums" :class="index <= phase ? 'bg-secondary/10 text-secondary' : 'bg-surface-container text-on-surface-variant'">{{ index + 1 }}</span>
          <p :class="index === phase ? 'font-semibold text-primary' : 'text-on-surface-variant'">{{ step }}</p>
        </li>
      </ol>
      <ShipmentRecord :shipment="shipment" />
      <p v-if="shipment.courier" class="text-on-surface-variant">Satria yang ditugaskan: <strong>{{ shipment.courier.name }}</strong></p>
      <template v-if="tab === 1">
        <section class="live-panel flex flex-col gap-4">
          <h2 class="text-xl font-semibold">Kesiapan penerima</h2>
          <p>
            {{
              shipment.recipient_ready
                ? 'Penerima sudah menyatakan siap.'
                : 'Penerima belum menyatakan siap.'
            }}
          </p>
          <button
            v-if="
              !['completed', 'delivered', 'returned', 'cancelled'].includes(
                shipment.status,
              )
            "
            type="button"
            class="live-button live-primary self-start"
            :disabled="mutation.busy.value"
            @click="readiness"
          >
            {{
              mutation.busy.value
                ? 'Menyimpan…'
                : shipment.recipient_ready
                  ? 'Batalkan kesiapan'
                  : 'Saya siap menerima'
            }}
          </button>
        </section>
        <section class="live-panel">
          <h2 class="text-xl font-semibold">Pemeriksaan awal</h2>
          <template v-if="shipment.inspection"
            ><p class="mt-3">
              {{ shipment.inspection.checker_name || 'Checker' }} ·
              {{
                shipment.inspection.passed
                  ? 'Memenuhi pemeriksaan'
                  : 'Ada temuan pemeriksaan'
              }}
            </p>
            <p class="mt-2">{{ shipment.inspection.note }}</p>
            <p class="mt-2">
              Suhu: {{ shipment.inspection.temperature }} °C · Segel
              {{ shipment.inspection.seal ? 'utuh' : 'bermasalah' }}
            </p>
            <details class="mt-4">
              <summary class="py-2 cursor-pointer">
                Lihat foto pemeriksaan
              </summary>
              <PrivateImage
                :path="base + '/inspection-photo'"
                alt="Foto pemeriksaan kiriman sendiri"
              /></details
          ></template>
          <p v-else class="mt-3">Belum ada pemeriksaan tersimpan.</p>
        </section>
      </template>
      <template v-else-if="tab === 2">
        <ApiState
          :loading="telemetry.loading.value || issues.loading.value"
          :error="telemetry.error.value || issues.error.value"
          @retry="reload"
        />
        <TelemetryRecords
          :samples="telemetry.data.value?.data || []"
          :minimum="shipment.temperature_min"
          :maximum="shipment.temperature_max"
        />
        <section class="live-panel">
          <h2 class="text-xl font-semibold">Kendala lapangan</h2>
          <p v-if="!issues.data.value?.data.length" class="mt-3">
            Belum ada kendala tercatat.
          </p>
          <article
            v-for="issue in issues.data.value?.data"
            :key="issue.id"
            class="py-4 border-b border-outline/15"
          >
            <p>{{ issue.type }} · {{ issue.status }}</p>
            <p class="mt-2">{{ issue.description }}</p>
            <p v-if="issue.resolution_reason" class="mt-2">
              {{ issue.resolution_reason }}
            </p>
          </article>
          <nav v-if="issues.data.value && issues.data.value.meta.last_page > 1" class="flex flex-wrap items-center gap-3 mt-4" aria-label="Halaman kendala">
            <button type="button" class="live-button" :disabled="issues.loading.value || issuesPage <= 1" @click="issuesPage--">Sebelumnya</button>
            <p class="tabular-nums">{{ issuesPage }} / {{ issues.data.value.meta.last_page }}</p>
            <button type="button" class="live-button" :disabled="issues.loading.value || issuesPage >= issues.data.value.meta.last_page" @click="issuesPage++">Berikutnya</button>
          </nav>
        </section>
      </template>
      <template v-else>
        <ApiState
          :loading="reports.loading.value || events.loading.value"
          :error="
            reports.error.value || events.error.value || proof.error.value
          "
          @retry="reload"
        />
        <section class="live-panel">
          <h2 class="text-xl font-semibold">Laporan pengiriman</h2>
          <p v-if="!reports.data.value?.data.length" class="mt-3">
            Laporan belum tersedia. PDF diterbitkan setelah serah terima
            selesai.
          </p>
          <article
            v-for="report in reports.data.value?.data"
            :key="report.id"
            class="py-4 border-b border-outline/15"
          >
            <p>{{ report.title }} · Revisi {{ report.revision }}</p>
            <p class="text-on-surface-variant mt-2 break-all">
              SHA-256: {{ report.snapshot_sha256 || report.sha256 }}
            </p>
            <button
              type="button"
              class="live-button mt-3"
              @click="downloadReport(report)"
            >
              Unduh PDF #{{ report.id }}
            </button>
          </article>
          <nav v-if="reports.data.value && reports.data.value.meta.last_page > 1" class="flex flex-wrap items-center gap-3 mt-4" aria-label="Halaman laporan">
            <button type="button" class="live-button" :disabled="reports.loading.value || reportsPage <= 1" @click="reportsPage--">Sebelumnya</button>
            <p class="tabular-nums">{{ reportsPage }} / {{ reports.data.value.meta.last_page }}</p>
            <button type="button" class="live-button" :disabled="reports.loading.value || reportsPage >= reports.data.value.meta.last_page" @click="reportsPage++">Berikutnya</button>
          </nav>
          <p class="mt-4 text-on-surface-variant">
            Laporan operasional dan checksum bukan sertifikasi BPOM atau tanda
            tangan digital tersertifikasi.
          </p>
        </section>
        <section v-if="proof.data.value" class="live-panel">
          <h2 class="text-xl font-semibold">Bukti serah terima</h2>
          <p class="mt-3">
            {{ proof.data.value.data.recipient_name }} ·
            {{ dateText(proof.data.value.data.handed_over_at) }}
          </p>
          <p class="mt-2">
            {{ proof.data.value.data.temperature }} °C ·
            {{
              proof.data.value.data.seal_intact
                ? 'Segel utuh'
                : 'Segel bermasalah'
            }}
          </p>
          <PrivateImage
            class="mt-4"
            :path="base + '/proof/photo'"
            alt="Foto bukti serah terima"
          /><PrivateImage
            v-if="proof.data.value.data.signature_url"
            class="mt-4"
            :path="base + '/proof/signature'"
            alt="Tanda tangan penerima"
          />
        </section>
        <section class="live-panel">
          <h2 class="text-xl font-semibold">Riwayat perjalanan</h2>
          <p v-if="!events.data.value?.data.length" class="mt-3">
            Belum ada peristiwa tercatat.
          </p>
          <ol role="list" class="mt-4 divide-y divide-outline/15">
            <li
              v-for="event in events.data.value?.data"
              :key="event.id"
              class="py-4"
            >
              <p class="font-semibold">{{ event.title }}</p>
              <p class="mt-1">{{ dateText(event.created_at) }}</p>
              <p v-if="event.reason" class="mt-1">{{ event.reason }}</p>
            </li>
          </ol>
          <nav v-if="events.data.value && events.data.value.meta.last_page > 1" class="flex flex-wrap items-center gap-3 mt-4" aria-label="Halaman linimasa">
            <button type="button" class="live-button" :disabled="events.loading.value || eventsPage <= 1" @click="eventsPage--">Sebelumnya</button>
            <p class="tabular-nums">{{ eventsPage }} / {{ events.data.value.meta.last_page }}</p>
            <button type="button" class="live-button" :disabled="events.loading.value || eventsPage >= events.data.value.meta.last_page" @click="eventsPage++">Berikutnya</button>
          </nav>
        </section>
      </template>
      <button type="button" class="live-button self-start" @click="reload">
        Muat ulang data resi
      </button>
    </template>
  </div>
</template>

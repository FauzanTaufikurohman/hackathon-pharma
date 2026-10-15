<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi, useMutation, usePolling } from '../../services/useApi'
import { download, errorText, type Page } from '../../services/api'
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
const record = useApi<Response<LiveShipment>>(() =>
  props.awb ? base.value : '',
)
const telemetry = useApi<Page<Sample>>(() =>
  props.awb && tab.value === 2 ? base.value + '/telemetry?per_page=100' : '',
)
const issues = useApi<Page<Issue>>(() =>
  props.awb && tab.value === 2 ? base.value + '/issues' : '',
)
const reports = useApi<Page<Report>>(() =>
  props.awb && tab.value === 3 ? base.value + '/reports' : '',
)
const events = useApi<Page<EventRecord>>(() =>
  props.awb && tab.value === 3 ? base.value + '/events?per_page=100' : '',
)
const shipment = computed(() => record.data.value?.data)
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
      <ShipmentRecord :shipment="shipment" />
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
        </section>
      </template>
      <button type="button" class="live-button self-start" @click="reload">
        Muat ulang data resi
      </button>
    </template>
  </div>
</template>

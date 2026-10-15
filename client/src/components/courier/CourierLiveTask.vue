<script setup lang="ts">
import { computed, onScopeDispose, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi, useMutation, usePolling } from '../../services/useApi'
import { download, errorText, type Page } from '../../services/api'
import {
  awbPath,
  dateText,
  type Response,
  type LiveTask,
  type Sample,
  type Issue,
} from '../../services/models'
import ApiState from '../layout/ApiState.vue'
import PrivateImage from '../layout/PrivateImage.vue'
import TelemetryRecords from '../layout/TelemetryRecords.vue'
const props = defineProps<{ awb: string }>()
const route = useRoute(),
  router = useRouter(),
  mutation = useMutation()
const tab = computed(() =>
  [1, 2, 3, 4].includes(Number(route.query.tab)) ? Number(route.query.tab) : 1,
)
const base = computed(() => '/courier/tasks/' + awbPath(props.awb))
const record = useApi<Response<LiveTask>>(() => (props.awb ? base.value : ''))
const task = computed(() => record.data.value?.data)
const telemetry = useApi<Page<Sample>>(() =>
  props.awb && tab.value === 2 ? base.value + '/telemetry?per_page=100' : '',
)
const issues = useApi<Page<Issue>>(() =>
  props.awb ? base.value + '/issues' : '',
)
interface Handover {
  task: LiveTask
  proof: {
    recipient_name: string
    temperature: number
    handed_over_at: string
    signature_url: string | null
  } | null
  report: { id: number; snapshot_sha256: string } | null
  can_submit: boolean
}
const handover = useApi<Response<Handover>>(() =>
  props.awb && tab.value === 4 ? base.value + '/handover' : '',
)
const checklist = reactive({
  seal: false,
  icepack: false,
  manifest: false,
  temperature: '' as number | string,
})
const reason = ref(''),
  category = ref('other'),
  description = ref(''),
  localError = ref(''),
  gpsBusy = ref(false)
const sensor = reactive({
  temperature: '' as number | string,
  latitude: '' as number | string,
  longitude: '' as number | string,
  sensor_id: '',
  battery_pct: '' as number | string,
})
const pod = reactive({
  recipient_name: '',
  recipient_department: '',
  recipient_identity: '',
  temperature: '' as number | string,
  seal_intact: false,
})
const issuePhoto = ref<File | null>(null),
  podPhoto = ref<File | null>(null),
  signature = ref<File | null>(null),
  preview = ref('')
let sampleTime = ''
usePolling(
  reload,
  () =>
    [2, 3].includes(tab.value) && !mutation.busy.value && !description.value,
)
onScopeDispose(() => {
  if (preview.value) URL.revokeObjectURL(preview.value)
})
const canReport = computed(
  () =>
    task.value &&
    [
      'accepted',
      'in_transit',
      'arrived',
      'held',
      'deviation_hold',
      'returning',
    ].includes(task.value.workflow_status),
)
const canReturn = computed(
  () =>
    task.value &&
    ['held', 'deviation_hold'].includes(task.value.workflow_status) &&
    issues.data.value?.data.some(
      (issue) => issue.decision?.action === 'return',
    ) &&
    !issues.data.value?.data.some((issue) => issue.status === 'open'),
)
async function reload() {
  await Promise.all([
    record.reload(),
    telemetry.reload(),
    issues.reload(),
    handover.reload(),
  ])
}
async function act(action: string, values: Record<string, unknown> = {}) {
  if (!task.value) return
  const result = await mutation.run(base.value + '/' + action, {
    version: task.value.version,
    ...values,
  })
  if (result) await reload()
  return result
}
function selectFile(event: Event, target: 'issue' | 'pod' | 'signature') {
  const file = (event.target as HTMLInputElement).files?.[0] || null
  localError.value = ''
  if (target === 'issue') issuePhoto.value = null
  else if (target === 'signature') signature.value = null
  else {
    podPhoto.value = null
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = ''
  }
  const maximum = target === 'signature' ? 1024 * 1024 : 5 * 1024 * 1024
  if (
    file &&
    (![
      'image/png',
      'image/jpeg',
      ...(target === 'signature' ? [] : ['image/webp']),
    ].includes(file.type) ||
      file.size > maximum)
  ) {
    localError.value =
      target === 'signature'
        ? 'Tanda tangan harus JPG/PNG maksimal 1 MB.'
        : 'Foto harus JPG/PNG/WebP maksimal 5 MB.'
    ;(event.target as HTMLInputElement).value = ''
    return
  }
  if (target === 'issue') issuePhoto.value = file
  else if (target === 'signature') signature.value = file
  else {
    podPhoto.value = file
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = file ? URL.createObjectURL(file) : ''
  }
}
function getGps() {
  localError.value = ''
  if (!navigator.geolocation) {
    localError.value =
      'Browser tidak mendukung GPS. Isi koordinat hasil pengukuran secara manual.'
    return
  }
  gpsBusy.value = true
  navigator.geolocation.getCurrentPosition(
    (position) => {
      sensor.latitude = position.coords.latitude
      sensor.longitude = position.coords.longitude
      gpsBusy.value = false
    },
    () => {
      localError.value =
        'Lokasi tidak dapat diakses. Periksa izin browser atau isi koordinat hasil pengukuran.'
      gpsBusy.value = false
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 },
  )
}
async function sample() {
  if (!sampleTime) sampleTime = new Date().toISOString()
  if (
    await act('telemetry', {
      temperature: Number(sensor.temperature),
      latitude: Number(sensor.latitude),
      longitude: Number(sensor.longitude),
      recorded_at: sampleTime,
      ...(sensor.sensor_id ? { sensor_id: sensor.sensor_id } : {}),
      ...(sensor.battery_pct !== ''
        ? { battery_pct: Number(sensor.battery_pct) }
        : {}),
    })
  )
    sampleTime = ''
}
async function reportIssue() {
  if (!task.value) return
  const data = new FormData()
  data.set('version', String(task.value.version))
  data.set('category', category.value)
  data.set('description', description.value)
  if (issuePhoto.value) data.set('photo', issuePhoto.value)
  if (sensor.latitude !== '' && sensor.longitude !== '') {
    data.set('latitude', String(sensor.latitude))
    data.set('longitude', String(sensor.longitude))
  }
  if (await mutation.run(base.value + '/issues', data)) {
    description.value = ''
    issuePhoto.value = null
    await reload()
  }
}
async function submitPod() {
  if (!task.value || !podPhoto.value) return
  const data = new FormData()
  data.set('version', String(task.value.version))
  data.set(
    'mode',
    task.value.workflow_status === 'returning' ? 'return' : 'normal',
  )
  data.set('recipient_name', pod.recipient_name)
  data.set('recipient_department', pod.recipient_department)
  data.set('recipient_identity', pod.recipient_identity)
  data.set('temperature', String(pod.temperature))
  data.set('seal_intact', pod.seal_intact ? '1' : '0')
  data.set('latitude', String(sensor.latitude))
  data.set('longitude', String(sensor.longitude))
  data.set('photo', podPhoto.value)
  if (signature.value) data.set('signature', signature.value)
  if (await mutation.run(base.value + '/handover', data)) await reload()
}
async function reportPdf() {
  const report = handover.data.value?.data.report
  if (!report) return
  try {
    await download(
      '/courier/reports/' + report.id + '/download',
      'laporan-' + report.id + '.pdf',
    )
  } catch (cause) {
    localError.value = errorText(cause)
  }
}
</script>
<template>
  <div class="flex flex-col gap-5">
    <nav
      aria-label="Tahap pelaksanaan tugas"
      class="flex gap-2 overflow-x-auto pb-2"
    >
      <button
        v-for="(name, index) in [
          'Tugas masuk',
          'Pengantaran & monitor',
          'Lapor kendala',
          'Serah terima & laporan',
        ]"
        :key="name"
        type="button"
        class="live-button whitespace-nowrap"
        :class="tab === index + 1 ? 'bg-primary/10 text-primary' : ''"
        :aria-current="tab === index + 1 ? 'page' : undefined"
        @click="
          router.replace({ query: { ...route.query, tab: String(index + 1) } })
        "
      >
        {{ name }}
      </button>
    </nav>
    <ApiState
      :loading="record.loading.value"
      :error="record.error.value || mutation.error.value || localError"
      :notice="mutation.notice.value"
      @retry="reload"
    />
    <section v-if="task" class="live-panel">
      <div class="flex flex-wrap justify-between gap-3">
        <div class="min-w-0">
          <p class="text-primary font-semibold break-all">{{ task.awb }}</p>
          <h1 class="text-2xl font-semibold mt-2">{{ task.item_title }}</h1>
        </div>
        <p class="live-badge bg-primary/10 text-primary self-start">
          {{ task.status_label }}
        </p>
      </div>
      <p class="mt-4">{{ task.origin_name }} → {{ task.destination_name }}</p>
      <p class="mt-2">{{ task.destination_address }}</p>
      <p class="mt-2">
        {{ task.weight_str }} · {{ task.temp_min }}–{{ task.temp_max }} °C ·
        {{ task.probe_temp_str }}
      </p>
      <p class="mt-2">{{ task.handling_instructions }}</p>
      <p v-if="task.is_demo" class="mt-3 text-tertiary">
        Tugas contoh; sampel demo tidak dikirim ke Telegram.
      </p>
    </section>
    <template v-if="task">
      <template v-if="tab === 1">
        <form
          v-if="task.workflow_status === 'assigned'"
          class="live-panel grid gap-4"
          @submit.prevent="
            act('accept', {
              pre_trip: {
                ...checklist,
                temperature: Number(checklist.temperature),
              },
            })
          "
        >
          <h2 class="text-xl font-semibold">
            Pemeriksaan sebelum menerima tugas
          </h2>
          <label class="flex items-center gap-3"
            ><input
              v-model="checklist.seal"
              name="seal"
              type="checkbox"
              required
              :disabled="mutation.busy.value"
            />Segel sudah diperiksa dan utuh</label
          >
          <label class="flex items-center gap-3"
            ><input
              v-model="checklist.icepack"
              name="icepack"
              type="checkbox"
              required
              :disabled="mutation.busy.value"
            />Pendingin dan kemasan sudah diperiksa</label
          >
          <label class="flex items-center gap-3"
            ><input
              v-model="checklist.manifest"
              name="manifest"
              type="checkbox"
              required
              :disabled="mutation.busy.value"
            />Manifest cocok dengan paket</label
          >
          <label class="live-field"
            ><span>Suhu pra-jalan (°C)</span
            ><input
              v-model.number="checklist.temperature"
              name="pre_trip_temperature"
              type="number"
              min="-100"
              max="100"
              step="0.1"
              required
              :disabled="mutation.busy.value"
          /></label>
          <button
            type="submit"
            class="live-button live-primary self-start"
            :disabled="mutation.busy.value"
          >
            Terima tugas ini
          </button>
        </form>
        <form
          v-if="task.workflow_status === 'assigned'"
          class="live-panel grid gap-4"
          @submit.prevent="act('reject', { reason })"
        >
          <h2 class="text-lg font-semibold">Tidak bisa mengambil tugas?</h2>
          <label class="live-field"
            ><span>Alasan penolakan</span
            ><textarea
              v-model="reason"
              name="reason"
              minlength="10"
              maxlength="2000"
              required
            /></label
          ><button
            type="submit"
            class="live-button self-start text-error"
            :disabled="mutation.busy.value"
          >
            Tolak penugasan ini
          </button>
        </form>
        <p v-else class="live-panel">
          Tahap saat ini: {{ task.status_label }}. Buka tab pengantaran atau
          serah terima untuk melanjutkan.
        </p>
      </template>
      <template v-else-if="tab === 2">
        <div class="flex flex-wrap gap-3">
          <button
            v-if="task.workflow_status === 'accepted'"
            type="button"
            class="live-button live-primary"
            :disabled="mutation.busy.value"
            @click="act('depart')"
          >
            Mulai berangkat
          </button>
          <button
            v-if="task.workflow_status === 'in_transit'"
            type="button"
            class="live-button"
            :disabled="mutation.busy.value"
            @click="act('arrive')"
          >
            Konfirmasi tiba di tujuan
          </button>
          <button
            v-if="canReturn"
            type="button"
            class="live-button"
            :disabled="mutation.busy.value"
            @click="act('confirm-return')"
          >
            Terima instruksi retur Admin
          </button>
        </div>
        <TelemetryRecords
          :samples="telemetry.data.value?.data || []"
          :minimum="task.temp_min"
          :maximum="task.temp_max"
        />
        <ApiState :error="telemetry.error.value" @retry="telemetry.reload" />
        <form
          v-if="
            [
              'in_transit',
              'arrived',
              'returning',
              'held',
              'deviation_hold',
            ].includes(task.workflow_status)
          "
          class="live-panel grid gap-4 sm:grid-cols-2"
          @submit.prevent="sample"
        >
          <div class="sm:col-span-2">
            <h2 class="text-xl font-semibold">Catat sampel perangkat</h2>
            <p class="mt-2">
              Isi hasil pengukuran sebenarnya. Jika memakai tugas demo, server
              menandainya sebagai simulasi.
            </p>
          </div>
          <label class="live-field"
            ><span>Suhu (°C)</span
            ><input
              v-model.number="sensor.temperature"
              name="temperature"
              type="number"
              min="-100"
              max="100"
              step="0.1"
              required
          /></label>
          <label class="live-field"
            ><span>ID sensor (opsional)</span
            ><input v-model="sensor.sensor_id" name="sensor_id" maxlength="100"
          /></label>
          <label class="live-field"
            ><span>Latitude</span
            ><input
              v-model.number="sensor.latitude"
              name="latitude"
              type="number"
              step="any"
              min="-90"
              max="90"
              required
          /></label>
          <label class="live-field"
            ><span>Longitude</span
            ><input
              v-model.number="sensor.longitude"
              name="longitude"
              type="number"
              step="any"
              min="-180"
              max="180"
              required
          /></label>
          <label class="live-field"
            ><span>Baterai (%) · opsional</span
            ><input
              v-model.number="sensor.battery_pct"
              name="battery_pct"
              type="number"
              step="1"
              min="0"
              max="100"
          /></label>
          <div class="flex flex-wrap items-end gap-3">
            <button
              type="button"
              class="live-button"
              :disabled="gpsBusy"
              @click="getGps"
            >
              {{
                gpsBusy ? 'Mengambil GPS…' : 'Gunakan lokasi perangkat'
              }}</button
            ><button
              type="submit"
              class="live-button"
              :disabled="mutation.busy.value"
            >
              Simpan sampel
            </button>
          </div>
        </form>
      </template>
      <template v-else-if="tab === 3">
        <ApiState
          :loading="issues.loading.value"
          :error="issues.error.value"
          @retry="issues.reload"
        />
        <section class="live-panel">
          <h2 class="text-xl font-semibold">
            Instruksi Admin dan riwayat kendala
          </h2>
          <p v-if="!issues.data.value?.data.length" class="mt-3">
            Belum ada kendala tercatat.
          </p>
          <article
            v-for="issue in issues.data.value?.data"
            :key="issue.id"
            class="py-4 border-b border-outline/15"
          >
            <p class="font-semibold">{{ issue.type }} · {{ issue.status }}</p>
            <p class="mt-2">{{ issue.description }}</p>
            <p v-if="issue.decision" class="mt-2 text-primary">
              Instruksi: {{ issue.decision.action }} ·
              {{ issue.decision.reason }}
            </p>
          </article>
          <button
            v-if="canReturn"
            type="button"
            class="live-button mt-4"
            :disabled="mutation.busy.value"
            @click="act('confirm-return')"
          >
            Terima instruksi retur Admin
          </button>
        </section>
        <form
          v-if="canReport"
          class="live-panel grid gap-4"
          @submit.prevent="reportIssue"
        >
          <h2 class="text-xl font-semibold">Laporkan kendala</h2>
          <label class="live-field"
            ><span>Jenis kendala</span
            ><select v-model="category" name="category">
              <option value="seal">Segel</option>
              <option value="temperature">Suhu</option>
              <option value="packaging">Kemasan</option>
              <option value="recipient_unavailable">Penerima belum ada</option>
              <option value="address">Alamat</option>
              <option value="other">Lainnya</option>
            </select></label
          >
          <label class="live-field"
            ><span>Yang terjadi di lapangan</span
            ><textarea
              v-model="description"
              name="description"
              minlength="10"
              maxlength="4000"
              required
              rows="3"
            />
          </label>
          <label class="live-field"
            ><span>Foto bukti (opsional, maksimal 5 MB)</span
            ><input
              type="file"
              name="issue_photo"
              accept="image/png,image/jpeg,image/webp"
              @change="(event) => selectFile(event, 'issue')"
          /></label>
          <button
            type="submit"
            class="live-button live-primary self-start"
            :disabled="mutation.busy.value"
          >
            Laporkan dan tahan kiriman
          </button>
          <p>
            Kiriman ditahan setelah laporan tersimpan. Tunggu keputusan Admin;
            jangan melanjutkan serah terima sendiri.
          </p>
        </form>
      </template>
      <template v-else>
        <ApiState
          :loading="handover.loading.value"
          :error="handover.error.value"
          @retry="handover.reload"
        />
        <form
          v-if="handover.data.value?.data.can_submit"
          class="live-panel grid gap-4 sm:grid-cols-2"
          @submit.prevent="submitPod"
        >
          <h2 class="text-xl font-semibold sm:col-span-2">
            {{
              task.workflow_status === 'returning'
                ? 'Bukti serah terima retur'
                : 'Bukti serah terima penerima'
            }}
          </h2>
          <label class="live-field"
            ><span>Nama penerima</span
            ><input
              v-model="pod.recipient_name"
              name="recipient_name"
              minlength="2"
              maxlength="255"
              required
          /></label>
          <label class="live-field"
            ><span>Bagian / unit (opsional)</span
            ><input
              v-model="pod.recipient_department"
              name="recipient_department"
              maxlength="255"
          /></label>
          <label class="live-field"
            ><span>Identitas petugas (opsional)</span
            ><input
              v-model="pod.recipient_identity"
              name="recipient_identity"
              maxlength="100"
          /></label>
          <label class="live-field"
            ><span>Suhu saat serah terima (°C)</span
            ><input
              v-model.number="pod.temperature"
              name="handover_temperature"
              type="number"
              min="-100"
              max="100"
              step="0.1"
              required
          /></label>
          <label class="live-field"
            ><span>Latitude serah terima</span
            ><input
              v-model.number="sensor.latitude"
              name="handover_latitude"
              type="number"
              min="-90"
              max="90"
              step="any"
              required
          /></label>
          <label class="live-field"
            ><span>Longitude serah terima</span
            ><input
              v-model.number="sensor.longitude"
              name="handover_longitude"
              type="number"
              min="-180"
              max="180"
              step="any"
              required
          /></label>
          <div class="sm:col-span-2">
            <button
              type="button"
              class="live-button"
              :disabled="gpsBusy"
              @click="getGps"
            >
              Gunakan lokasi perangkat
            </button>
          </div>
          <label class="flex items-center gap-3 sm:col-span-2"
            ><input
              v-model="pod.seal_intact"
              name="seal_intact"
              type="checkbox"
            />Segel utuh saat diserahkan</label
          >
          <label class="live-field"
            ><span>Foto serah terima · wajib</span
            ><input
              name="pod_photo"
              type="file"
              accept="image/png,image/jpeg,image/webp"
              required
              @change="(event) => selectFile(event, 'pod')"
          /></label>
          <label class="live-field"
            ><span>Tanda tangan gambar · opsional, maksimal 1 MB</span
            ><input
              name="signature"
              type="file"
              accept="image/png,image/jpeg"
              @change="(event) => selectFile(event, 'signature')"
          /></label>
          <img
            v-if="preview"
            :src="preview"
            alt="Pratinjau bukti serah terima"
            class="max-h-60 object-contain sm:col-span-2"
          />
          <p class="sm:col-span-2">
            Setelah tersimpan, kiriman ditutup dan bukti tidak dapat diedit.
            Periksa resi, penerima, dan foto sebelum mengirim.
          </p>
          <button
            type="submit"
            class="live-button live-primary justify-self-start"
            :disabled="mutation.busy.value || !podPhoto"
          >
            Simpan bukti dan tutup kiriman
          </button>
        </form>
        <section v-else-if="handover.data.value?.data.proof" class="live-panel">
          <h2 class="text-xl font-semibold">Serah terima selesai</h2>
          <p class="mt-3">
            {{ handover.data.value.data.proof.recipient_name }} ·
            {{ dateText(handover.data.value.data.proof.handed_over_at) }}
          </p>
          <PrivateImage
            class="mt-4"
            :path="base + '/proof/photo'"
            alt="Bukti serah terima tersimpan"
          /><PrivateImage
            v-if="handover.data.value.data.proof.signature_url"
            class="mt-4"
            :path="base + '/proof/signature'"
            alt="Tanda tangan penerima"
          /><button
            v-if="handover.data.value.data.report"
            type="button"
            class="live-button mt-4"
            @click="reportPdf"
          >
            Unduh PDF laporan
          </button>
        </section>
        <p v-else class="live-panel">
          Serah terima belum tersedia pada tahap {{ task.status_label }}.
          Pastikan sudah tiba dan tidak ada kendala terbuka.
        </p>
      </template>
    </template>
    <button
      type="button"
      class="live-button self-start"
      :disabled="record.loading.value || mutation.busy.value"
      @click="reload"
    >
      Muat ulang tugas
    </button>
  </div>
</template>

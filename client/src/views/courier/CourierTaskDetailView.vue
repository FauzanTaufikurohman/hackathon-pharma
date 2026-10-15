<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { download, type Page } from '../../services/api'
import { useApi, useMutation, usePolling } from '../../services/useApi'
import { awbPath, dateText, type LiveTask, type Response, type Sample } from '../../services/models'
import ApiState from '../../components/layout/ApiState.vue'
import RoleNavbar from '../../components/layout/RoleNavbar.vue'
import RoleFooter from '../../components/layout/RoleFooter.vue'

interface IssueItem {
  id: number
  type: string
  status: string
  description: string
  created_at: string
  decision: { action: string; reason: string } | null
}
interface MonitorData {
  task: LiveTask
  recipient: { recipient_name: string; recipient_phone: string; address: string }
  open_issues_count: number
  can_depart: boolean
  can_arrive: boolean
  can_handover: boolean
}
interface HandoverData {
  task: LiveTask
  proof: {
    recipient_name: string
    temperature: number
    seal_intact: boolean
    handed_over_at: string
  } | null
  report: { id: number; revision: number; snapshot_sha256: string } | null
  can_submit: boolean
}

const route = useRoute()
const awb = computed(() => String(route.params.awb || ''))
const base = computed(() => '/courier/tasks/' + awbPath(awb.value))
const mutation = useMutation()
const task = useApi<Response<LiveTask>>(() => awb.value ? base.value : '')
const monitor = useApi<Response<MonitorData>>(() => awb.value ? base.value + '/monitor' : '')
const samples = useApi<Page<Sample>>(() => awb.value ? base.value + '/telemetry?per_page=50' : '')
const issues = useApi<Page<IssueItem>>(() => awb.value ? base.value + '/issues?per_page=50' : '')
const handover = useApi<Response<HandoverData>>(() => awb.value ? base.value + '/handover' : '')
const shipment = computed(() => task.data.value?.data)
const latestSample = computed(() => samples.data.value?.data.at(-1))
const preTrip = ref({ seal: false, icepack: false, manifest: false, temperature: 4 })
const telemetryInput = ref({ temperature: 4, latitude: '', longitude: '' })
const issueInput = ref({ category: 'temperature', description: '' })
const issuePhoto = ref<File | null>(null)
const handoverInput = ref({
  recipient_name: '',
  temperature: 4,
  seal_intact: true,
  latitude: '',
  longitude: '',
})
const handoverPhoto = ref<File | null>(null)
const locationError = ref('')
const handoverError = ref('')
const reportError = ref('')
const allChecked = computed(() => preTrip.value.seal && preTrip.value.icepack && preTrip.value.manifest)
const workflowStatus = computed(() => shipment.value?.workflow_status || '')
const activeIssues = computed(() => issues.data.value?.data || [])
const returnDecision = computed(() =>
  activeIssues.value.find((item) => item.decision?.action === 'return' && item.status === 'resolved'),
)
const canCaptureTelemetry = computed(() =>
  ['in_transit', 'arrived', 'returning'].includes(workflowStatus.value) &&
  !monitor.data.value?.data.open_issues_count,
)

async function reload() {
  await Promise.all([task.reload(), monitor.reload(), samples.reload(), issues.reload(), handover.reload()])
}
usePolling(reload, () => !mutation.busy.value)

async function mutate(path: string, values: Record<string, unknown> | FormData) {
  if (await mutation.run(path, values)) await reload()
}
async function acceptTask() {
  if (!shipment.value || !allChecked.value) return
  await mutate(base.value + '/accept', {
    version: shipment.value.version,
    pre_trip: { ...preTrip.value },
  })
}
async function rejectTask() {
  if (!shipment.value) return
  await mutate(base.value + '/reject', {
    version: shipment.value.version,
    category: 'equipment_failure',
    notes: issueInput.value.description,
  })
}
async function depart() {
  if (!shipment.value || !monitor.data.value?.data.can_depart) return
  await mutate(base.value + '/depart', { version: shipment.value.version })
}
async function arrive() {
  if (!shipment.value || !monitor.data.value?.data.can_arrive) return
  const values: Record<string, unknown> = { version: shipment.value.version }
  const latitude = Number(telemetryInput.value.latitude)
  const longitude = Number(telemetryInput.value.longitude)
  if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
    values.latitude = latitude
    values.longitude = longitude
  }
  await mutate(base.value + '/arrive', values)
}
async function recordTelemetry() {
  if (!shipment.value || !canCaptureTelemetry.value) return
  await mutate(base.value + '/telemetry', {
    temperature: telemetryInput.value.temperature,
    latitude: Number(telemetryInput.value.latitude),
    longitude: Number(telemetryInput.value.longitude),
    recorded_at: new Date().toISOString(),
  })
}
async function captureLocation() {
  locationError.value = ''
  if (!navigator.geolocation) {
    locationError.value = 'Browser ini tidak menyediakan akses GPS.'
    return
  }
  navigator.geolocation.getCurrentPosition(
    ({ coords }) => {
      telemetryInput.value.latitude = coords.latitude.toFixed(7)
      telemetryInput.value.longitude = coords.longitude.toFixed(7)
      handoverInput.value.latitude = telemetryInput.value.latitude
      handoverInput.value.longitude = telemetryInput.value.longitude
    },
    () => {
      locationError.value = 'Lokasi tidak dapat diambil. Periksa izin GPS atau masukkan koordinat secara manual.'
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 },
  )
}
async function submitIssue() {
  if (!shipment.value) return
  const form = new FormData()
  form.set('version', String(shipment.value.version))
  form.set('category', issueInput.value.category)
  form.set('description', issueInput.value.description)
  if (issuePhoto.value) form.set('photo', issuePhoto.value)
  if (telemetryInput.value.latitude && telemetryInput.value.longitude) {
    form.set('latitude', telemetryInput.value.latitude)
    form.set('longitude', telemetryInput.value.longitude)
  }
  await mutate(base.value + '/issues', form)
  if (!mutation.error.value) {
    issueInput.value.description = ''
    issuePhoto.value = null
  }
}
async function confirmReturn() {
  if (!shipment.value) return
  await mutate(base.value + '/confirm-return', { version: shipment.value.version })
}
async function submitHandover() {
  if (!shipment.value || !handoverPhoto.value) return
  handoverError.value = ''
  const form = new FormData()
  form.set('version', String(shipment.value.version))
  form.set('mode', workflowStatus.value === 'returning' ? 'return' : 'normal')
  form.set('recipient_name', handoverInput.value.recipient_name)
  form.set('temperature', String(handoverInput.value.temperature))
  form.set('seal_intact', handoverInput.value.seal_intact ? '1' : '0')
  form.set('latitude', handoverInput.value.latitude)
  form.set('longitude', handoverInput.value.longitude)
  form.set('photo', handoverPhoto.value)
  await mutate(base.value + '/handover', form)
  if (!mutation.error.value) handoverPhoto.value = null
}
async function downloadReport() {
  const report = handover.data.value?.data.report
  if (!report) return
  reportError.value = ''
  try {
    await download('/courier/reports/' + report.id + '/download', 'laporan-' + awb.value + '.pdf')
  } catch (error) {
    reportError.value = error instanceof Error ? error.message : 'Laporan belum dapat diunduh.'
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <RoleNavbar role="courier" />
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 py-8 flex-1 flex flex-col gap-6">
    <header>
      <p class="text-primary font-semibold">Tugas kurir</p>
      <h1 class="text-3xl font-semibold tracking-tight mt-2">
        {{ shipment?.awb || 'Memuat kiriman…' }}
      </h1>
      <p class="mt-3 text-on-surface-variant">
        Penerimaan tugas, perjalanan GPS dan suhu, pelaporan kendala, lalu bukti serah terima.
      </p>
    </header>
    <ApiState
      :loading="task.loading.value && !shipment"
      :error="task.error.value || mutation.error.value || monitor.error.value || samples.error.value || issues.error.value || handover.error.value || reportError"
      :notice="mutation.notice.value"
      @retry="reload"
    />

    <template v-if="shipment">
      <section class="live-panel">
        <div class="flex flex-wrap justify-between gap-4">
          <div>
            <p class="text-on-surface-variant">{{ shipment.item_title }}</p>
            <h2 class="text-xl font-semibold mt-1">{{ shipment.status_label }}</h2>
          </div>
          <p class="live-badge">{{ shipment.is_demo ? 'DATA DEMO' : 'KIRIMAN OPERASIONAL' }}</p>
        </div>
        <dl class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
          <div><dt>Asal</dt><dd>{{ shipment.origin_name }} · {{ shipment.origin_detail }}</dd></div>
          <div><dt>Tujuan</dt><dd>{{ shipment.destination_name }} · {{ shipment.destination_detail }}</dd></div>
          <div><dt>Rentang suhu</dt><dd>{{ shipment.temp_min }}–{{ shipment.temp_max }} °C</dd></div>
          <div><dt>Sampel terakhir</dt><dd>{{ shipment.temp_actual ?? '—' }} °C · {{ dateText(shipment.telemetry_recorded_at) }}</dd></div>
        </dl>
        <p class="mt-4">{{ shipment.destination_address }}</p>
        <p class="mt-2 text-on-surface-variant">{{ shipment.handling_instructions }}</p>
      </section>

      <section v-if="workflowStatus === 'assigned'" class="live-panel">
        <h2 class="text-xl font-semibold">Konfirmasi tugas dan pemeriksaan pra-jalan</h2>
        <p class="mt-2 text-on-surface-variant">
          Tugas baru ditugaskan kepada akun kurir ini. Tiga checklist dan suhu cold-box harus lolos sebelum berangkat.
        </p>
        <div class="grid sm:grid-cols-3 gap-3 mt-5">
          <label><input v-model="preTrip.seal" type="checkbox" /> Segel dan box utuh</label>
          <label><input v-model="preTrip.icepack" type="checkbox" /> Icepack siap</label>
          <label><input v-model="preTrip.manifest" type="checkbox" /> Manifest sesuai</label>
        </div>
        <label class="live-field mt-4">
          <span>Suhu sebelum berangkat (°C)</span>
          <input v-model.number="preTrip.temperature" type="number" step="0.1" :min="shipment.temp_min" :max="shipment.temp_max" required />
        </label>
        <div class="flex flex-wrap gap-3 mt-5">
          <button class="live-button live-primary" :disabled="!allChecked || mutation.busy.value" @click="acceptTask">Terima penugasan</button>
        </div>
        <form class="grid gap-3 mt-6 border-t border-outline/15 pt-5" @submit.prevent="rejectTask">
          <label class="live-field">
            <span>Alasan menolak tugas</span>
            <textarea v-model="issueInput.description" minlength="5" maxlength="2000" required />
          </label>
          <button class="live-button self-start" :disabled="mutation.busy.value">Tolak tugas dengan alasan</button>
        </form>
      </section>

      <section v-if="monitor.data.value?.data.can_depart" class="live-panel">
        <h2 class="text-xl font-semibold">Siap memulai perjalanan</h2>
        <p class="mt-2">Setelah berangkat, catat sampel suhu dan lokasi selama perjalanan.</p>
        <button class="live-button live-primary mt-4" :disabled="mutation.busy.value" @click="depart">Mulai pengantaran</button>
      </section>

      <section v-if="canCaptureTelemetry" class="live-panel">
        <h2 class="text-xl font-semibold">Monitor suhu dan lokasi</h2>
        <p class="mt-2 text-on-surface-variant">
          {{ shipment.is_demo ? 'Data ini ditandai simulasi.' : 'Pastikan data suhu dan koordinat berasal dari perangkat/pencatatan yang benar.' }}
        </p>
        <div class="grid sm:grid-cols-3 gap-4 mt-4">
          <label class="live-field"><span>Suhu (°C)</span><input v-model.number="telemetryInput.temperature" type="number" min="-100" max="100" step="0.1" required /></label>
          <label class="live-field"><span>Latitude</span><input v-model="telemetryInput.latitude" type="number" min="-90" max="90" step="any" required /></label>
          <label class="live-field"><span>Longitude</span><input v-model="telemetryInput.longitude" type="number" min="-180" max="180" step="any" required /></label>
        </div>
        <p v-if="locationError" class="text-error mt-3" role="alert">{{ locationError }}</p>
        <div class="flex flex-wrap gap-3 mt-4">
          <button type="button" class="live-button" @click="captureLocation">Ambil lokasi GPS</button>
          <button type="button" class="live-button live-primary" :disabled="mutation.busy.value" @click="recordTelemetry">Simpan sampel monitor</button>
          <button v-if="monitor.data.value?.data.can_arrive" type="button" class="live-button" :disabled="mutation.busy.value" @click="arrive">Konfirmasi tiba</button>
        </div>
      </section>

      <section class="live-panel">
        <h2 class="text-xl font-semibold">Riwayat telemetry</h2>
        <p v-if="!samples.data.value?.data.length" class="mt-3 text-on-surface-variant">Belum ada sampel telemetry.</p>
        <ol v-else class="mt-4 divide-y divide-outline/15">
          <li v-for="sample in [...samples.data.value.data].reverse()" :key="sample.id" class="py-3 flex flex-wrap justify-between gap-2">
            <span>{{ dateText(sample.recorded_at) }} · {{ sample.temperature ?? '—' }} °C</span>
            <span>{{ sample.latitude }}, {{ sample.longitude }}{{ sample.is_simulated ? ' · simulasi' : '' }}</span>
          </li>
        </ol>
        <p v-if="latestSample" class="mt-3 text-on-surface-variant">
          Titik terakhir: {{ latestSample.latitude }}, {{ latestSample.longitude }}
        </p>
      </section>

      <section v-if="!['assigned', 'completed', 'delivered', 'returned', 'cancelled'].includes(workflowStatus)" class="live-panel">
        <h2 class="text-xl font-semibold">Laporkan kendala</h2>
        <p class="mt-2 text-on-surface-variant">Kendala akan menahan pengiriman dan diteruskan ke Admin untuk keputusan.</p>
        <form class="grid gap-4 mt-4" @submit.prevent="submitIssue">
          <label class="live-field">
            <span>Jenis kendala</span>
            <select v-model="issueInput.category">
              <option value="temperature">Suhu</option><option value="seal">Segel</option>
              <option value="packaging">Kemasan</option><option value="recipient_unavailable">Penerima tidak tersedia</option>
              <option value="address">Alamat</option><option value="other">Lainnya</option>
            </select>
          </label>
          <label class="live-field"><span>Deskripsi</span><textarea v-model="issueInput.description" minlength="10" maxlength="4000" required /></label>
          <label class="live-field"><span>Foto bukti (opsional)</span><input type="file" accept="image/jpeg,image/png,image/webp" @change="issuePhoto = ($event.target as HTMLInputElement).files?.[0] || null" /></label>
          <button class="live-button live-primary self-start" :disabled="mutation.busy.value">Kirim laporan kendala</button>
        </form>
      </section>

      <section v-if="activeIssues.length" class="live-panel">
        <h2 class="text-xl font-semibold">Kendala dan keputusan Admin</h2>
        <article v-for="issue in activeIssues" :key="issue.id" class="border-b border-outline/15 py-4">
          <p class="font-semibold">#{{ issue.id }} · {{ issue.type }} · {{ issue.status }}</p>
          <p class="mt-2">{{ issue.description }}</p>
          <p v-if="issue.decision" class="mt-2">Keputusan {{ issue.decision.action }}: {{ issue.decision.reason }}</p>
        </article>
        <button v-if="workflowStatus === 'deviation_hold' && returnDecision" class="live-button mt-4" :disabled="mutation.busy.value" @click="confirmReturn">Konfirmasi instruksi retur Admin</button>
      </section>

      <section v-if="monitor.data.value?.data.can_handover && handover.data.value?.data.can_submit" class="live-panel">
        <h2 class="text-xl font-semibold">{{ workflowStatus === 'returning' ? 'Serah terima retur' : 'Konfirmasi serah terima' }}</h2>
        <p class="mt-2 text-on-surface-variant">
          Laporan PDF dan antrean notifikasi dibuat setelah serah terima berhasil tersimpan.
        </p>
        <form class="grid gap-4 mt-4" @submit.prevent="submitHandover">
          <label class="live-field"><span>Nama penerima</span><input v-model="handoverInput.recipient_name" minlength="2" maxlength="255" required /></label>
          <label class="live-field"><span>Suhu saat serah terima (°C)</span><input v-model.number="handoverInput.temperature" type="number" min="-100" max="100" step="0.1" required /></label>
          <label class="flex items-center gap-2"><input v-model="handoverInput.seal_intact" type="checkbox" /> Segel utuh</label>
          <div class="grid sm:grid-cols-2 gap-4">
            <label class="live-field"><span>Latitude</span><input v-model="handoverInput.latitude" type="number" min="-90" max="90" step="any" required /></label>
            <label class="live-field"><span>Longitude</span><input v-model="handoverInput.longitude" type="number" min="-180" max="180" step="any" required /></label>
          </div>
          <button type="button" class="live-button self-start" @click="captureLocation">Isi lokasi saat ini</button>
          <label class="live-field"><span>Foto bukti serah terima (wajib)</span><input type="file" accept="image/jpeg,image/png,image/webp" required @change="handoverPhoto = ($event.target as HTMLInputElement).files?.[0] || null" /></label>
          <p v-if="handoverError" class="text-error" role="alert">{{ handoverError }}</p>
          <button class="live-button live-primary self-start" :disabled="!handoverPhoto || mutation.busy.value">Selesaikan dan terbitkan laporan</button>
        </form>
      </section>

      <section v-if="handover.data.value?.data.report" class="live-panel">
        <h2 class="text-xl font-semibold">Pengiriman selesai</h2>
        <p class="mt-2">Laporan #{{ handover.data.value.data.report.id }} · revisi {{ handover.data.value.data.report.revision }}</p>
        <p class="mt-2 break-all text-on-surface-variant">SHA-256: {{ handover.data.value.data.report.snapshot_sha256 }}</p>
        <button class="live-button live-primary mt-4" @click="downloadReport">Unduh laporan PDF</button>
        <p class="mt-3 text-on-surface-variant">Notifikasi laporan masuk antrean. Pengiriman Telegram bergantung pada konfigurasi gateway dan penerima.</p>
      </section>
    </template>
    </main>
    <RoleFooter role="courier" />
  </div>
</template>

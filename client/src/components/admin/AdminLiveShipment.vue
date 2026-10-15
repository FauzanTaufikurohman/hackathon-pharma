<script setup lang="ts">
import { computed, ref } from 'vue'
import { useApi, useMutation, usePolling } from '../../services/useApi'
import {
  awbPath,
  terminal,
  type LiveShipment,
  type Lookups,
  type Response,
  type Issue,
  type Sample,
} from '../../services/models'
import { download, errorText, query, type Page } from '../../services/api'
import ApiState from '../layout/ApiState.vue'
import ShipmentRecord from '../layout/ShipmentRecord.vue'
import PrivateImage from '../layout/PrivateImage.vue'
import TelemetryRecords from '../layout/TelemetryRecords.vue'
const props = defineProps<{
  awb: string
  mode?: 'approval' | 'monitor' | 'summary'
}>()
const emit = defineEmits<{ changed: [] }>()
const base = computed(() => '/admin/shipments/' + awbPath(props.awb))
const record = useApi<Response<LiveShipment>>(() =>
  props.awb ? base.value : '',
)
const lookups = useApi<Response<Lookups>>(() =>
  props.mode === 'approval' ? '/admin/lookups' : '',
)
const issues = useApi<Page<Issue>>(() =>
  props.awb && props.mode === 'monitor'
    ? '/admin/issues' + query({ awb: props.awb })
    : '',
)
const telemetry = useApi<Page<Sample>>(() =>
  props.awb && props.mode === 'monitor'
    ? base.value + '/telemetry?per_page=100'
    : '',
)
const prediction = useApi<
  Response<{
    available: boolean
    reason?: string
    predicted_temperature?: number
    minutes_to_upper_limit?: number
    status?: string
  }>
>(() =>
  props.awb && props.mode === 'monitor' ? base.value + '/prediction' : '',
)
const mutation = useMutation(),
  reason = ref(''),
  decision = ref('held'),
  courier = ref(''),
  issueId = ref(''),
  issueAction = ref('recheck'),
  confirmed = ref(false),
  downloadError = ref('')
const shipment = computed(() => record.data.value?.data)
const simulationType = ref('temperature'),
  simulationTemperature = ref(10)
usePolling(
  reload,
  () => props.mode === 'monitor' && !mutation.busy.value && !reason.value,
)
const openIssues = computed(
  () => issues.data.value?.data.filter((item) => item.status === 'open') || [],
)
async function reload() {
  await Promise.all([
    record.reload(),
    issues.reload(),
    telemetry.reload(),
    prediction.reload(),
  ])
}
async function act(action: string, extra: Record<string, unknown> = {}) {
  if (!shipment.value) return
  const result = await mutation.run(base.value + '/' + action, {
    version: shipment.value.version,
    reason: reason.value,
    ...extra,
  })
  if (result) {
    reason.value = ''
    confirmed.value = false
    await reload()
    emit('changed')
  }
}
async function report(id: number) {
  downloadError.value = ''
  try {
    await download(
      '/admin/reports/' + id + '/download',
      'laporan-' + id + '.pdf',
    )
  } catch (cause) {
    downloadError.value = errorText(cause)
  }
}
const predictionMessage = computed(() =>
  prediction.data.value?.data.available
    ? 'Tren dihitung dari sampel tersimpan. Periksa grafik sebelum membuat keputusan.'
    : prediction.data.value?.data.reason ||
      'Data belum cukup untuk menghitung tren.',
)
</script>
<template>
  <div class="flex flex-col gap-5 min-w-0">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h2 class="text-xl font-semibold">Detail kiriman</h2>
      <button
        type="button"
        class="live-button"
        :disabled="record.loading.value || mutation.busy.value"
        @click="reload"
      >
        Muat ulang detail
      </button>
    </div>
    <ApiState
      :loading="record.loading.value"
      :error="record.error.value || mutation.error.value || downloadError"
      :notice="mutation.notice.value"
      @retry="reload"
    />
    <template v-if="shipment">
      <ShipmentRecord :shipment="shipment" />
      <section v-if="shipment.inspection" class="live-panel">
        <h3 class="text-lg font-semibold">Pemeriksaan terakhir</h3>
        <p class="mt-3">
          Kemasan: {{ shipment.inspection.packaging ? 'utuh' : 'bermasalah' }} ·
          Segel: {{ shipment.inspection.seal ? 'utuh' : 'bermasalah' }} · Label:
          {{ shipment.inspection.label ? 'sesuai' : 'bermasalah' }}
        </p>
        <p class="mt-2">
          {{ shipment.inspection.temperature }} °C ·
          {{
            shipment.inspection.passed
              ? 'Memenuhi pemeriksaan'
              : 'Tidak memenuhi pemeriksaan'
          }}
        </p>
        <p class="mt-2">{{ shipment.inspection.note }}</p>
        <details class="mt-4">
          <summary class="cursor-pointer py-2">Lihat foto pemeriksaan</summary>
          <PrivateImage
            :path="base + '/inspection-photo'"
            alt="Bukti pemeriksaan kiriman"
          />
        </details>
      </section>
      <form
        v-if="mode === 'approval' && !terminal(shipment.status)"
        class="live-panel flex flex-col gap-4"
        @submit.prevent="
          shipment.status === 'pending_approval'
            ? act('approval', { decision })
            : shipment.status === 'approved'
              ? act('assign', { courier_id: Number(courier) })
              : act('recheck')
        "
      >
        <h3 class="text-lg font-semibold">Persetujuan dan penugasan</h3>
        <label v-if="shipment.status === 'pending_approval'" class="live-field"
          ><span>Keputusan pemeriksaan</span
          ><select v-model="decision" name="decision">
            <option value="held">Tahan untuk diperiksa</option>
            <option value="approved" :disabled="!shipment.inspection?.passed">
              Setujui hasil pemeriksaan
            </option>
          </select></label
        >
        <label v-if="shipment.status === 'approved'" class="live-field"
          ><span>Kurir aktif</span
          ><select v-model="courier" name="courier_id" required>
            <option disabled value="">Pilih kurir</option>
            <option
              v-for="item in lookups.data.value?.data.couriers"
              :key="item.id"
              :value="item.id"
            >
              {{ item.name }} · {{ item.reserved_kg }}/{{
                item.capacity_kg ?? 'belum diatur'
              }}
              kg
            </option>
          </select></label
        >
        <p
          v-if="
            !['pending_approval', 'approved', 'held'].includes(shipment.status)
          "
        >
          Tahap {{ shipment.status_label }} belum memerlukan keputusan QC atau
          penugasan baru.
        </p>
        <template v-else>
          <label class="live-field"
            ><span>Alasan / catatan keputusan</span
            ><textarea
              v-model="reason"
              name="reason"
              minlength="5"
              maxlength="4000"
              required
              rows="3"
            />
          </label>
          <button
            type="submit"
            class="live-button live-primary self-start"
            :disabled="mutation.busy.value"
          >
            {{
              mutation.busy.value
                ? 'Menyimpan…'
                : shipment.status === 'pending_approval'
                  ? 'Simpan keputusan'
                  : shipment.status === 'approved'
                    ? 'Tugaskan kurir'
                    : 'Minta pemeriksaan ulang'
            }}
          </button>
        </template>
        <ApiState :error="lookups.error.value" @retry="lookups.reload" />
      </form>
      <template v-if="mode === 'monitor'">
        <p class="text-on-surface-variant">
          Data diperbarui tiap 15 detik saat halaman aktif. Pembaruan dijeda
          ketika Anda menulis keputusan.
        </p>
        <details v-if="shipment.can_simulate" class="live-panel">
          <summary class="cursor-pointer font-semibold">
            Uji skenario pada kiriman contoh
          </summary>
          <form
            class="grid gap-4 mt-4"
            @submit.prevent="
              act('simulate', {
                type: simulationType,
                ...(simulationType === 'temperature'
                  ? { temperature: simulationTemperature }
                  : {}),
              })
            "
          >
            <p>
              Hanya untuk data demo. Tidak mengubah kiriman nyata atau mengirim
              pesan Telegram.
            </p>
            <label class="live-field"
              ><span>Skenario uji</span
              ><select v-model="simulationType" name="simulation_type">
                <option value="temperature">Suhu melewati batas</option>
                <option value="signal_loss">Sinyal tidak tersedia</option>
                <option value="seal">Segel bermasalah</option>
              </select></label
            >
            <label v-if="simulationType === 'temperature'" class="live-field"
              ><span>Suhu contoh (°C)</span
              ><input
                v-model.number="simulationTemperature"
                name="simulation_temperature"
                type="number"
                min="-100"
                max="100"
                step="0.1"
                required
            /></label>
            <button
              type="submit"
              class="live-button self-start"
              :disabled="mutation.busy.value"
            >
              Jalankan skenario demo
            </button>
          </form>
        </details>
        <ApiState
          :error="
            telemetry.error.value ||
            issues.error.value ||
            prediction.error.value
          "
          @retry="reload"
        />
        <TelemetryRecords
          :samples="telemetry.data.value?.data || []"
          :minimum="shipment.temperature_min"
          :maximum="shipment.temperature_max"
        />
        <section class="live-panel">
          <h3 class="text-lg font-semibold">Tren suhu</h3>
          <p class="mt-3">{{ predictionMessage }}</p>
          <p
            v-if="
              prediction.data.value?.data.available &&
              prediction.data.value.data.minutes_to_upper_limit != null
            "
            class="mt-2"
          >
            Perkiraan menuju batas atas:
            {{ prediction.data.value.data.minutes_to_upper_limit }} menit. Bukan
            rekomendasi medis atau bukti sensor terkalibrasi.
          </p>
        </section>
        <section class="live-panel">
          <h3 class="text-lg font-semibold">Kendala kiriman</h3>
          <p v-if="!issues.data.value?.data.length" class="mt-3">
            Belum ada kendala tercatat.
          </p>
          <article
            v-for="issue in issues.data.value?.data"
            :key="issue.id"
            class="py-4 border-b border-outline/15"
          >
            <p class="font-semibold">
              #{{ issue.id }} · {{ issue.type }} · {{ issue.status }}
            </p>
            <p class="mt-2">{{ issue.description }}</p>
            <p v-if="issue.resolution_reason" class="mt-2">
              Keputusan: {{ issue.latest_decision?.action }} ·
              {{ issue.resolution_reason }}
            </p>
            <details v-if="issue.photo_path" class="mt-2">
              <summary class="cursor-pointer">Lihat bukti kendala</summary>
              <PrivateImage
                :path="'/admin/issues/' + issue.id + '/photo'"
                alt="Bukti kendala kiriman"
              />
            </details>
          </article>
          <form
            v-if="openIssues.length"
            class="grid gap-4 mt-5"
            @submit.prevent="
              act('issues/' + issueId + '/decision', { action: issueAction })
            "
          >
            <label class="live-field"
              ><span>Kendala yang ditangani</span
              ><select v-model="issueId" name="issue_id" required>
                <option disabled value="">Pilih kendala</option>
                <option
                  v-for="issue in openIssues"
                  :key="issue.id"
                  :value="issue.id"
                >
                  #{{ issue.id }} · {{ issue.type }}
                </option>
              </select></label
            >
            <label class="live-field"
              ><span>Tindak lanjut</span
              ><select v-model="issueAction" name="action">
                <option value="recheck">Periksa ulang di hub</option>
                <option value="return">Instruksikan retur</option>
                <option value="resume">Lanjutkan setelah aman</option>
              </select></label
            >
            <label class="live-field"
              ><span>Alasan keputusan</span
              ><textarea
                v-model="reason"
                name="reason"
                minlength="5"
                maxlength="4000"
                required
              />
            </label>
            <button
              type="submit"
              class="live-button live-primary self-start"
              :disabled="mutation.busy.value"
            >
              Simpan keputusan kendala
            </button>
          </form>
        </section>
      </template>
      <section v-if="shipment.report_ids?.length" class="live-panel">
        <h3 class="text-lg font-semibold">Laporan tersedia</h3>
        <div class="flex flex-wrap gap-3 mt-4">
          <button
            v-for="id in shipment.report_ids"
            :key="id"
            type="button"
            class="live-button"
            @click="report(id)"
          >
            Unduh PDF #{{ id }}
          </button>
        </div>
      </section>
      <details
        v-if="
          mode === 'summary' &&
          [
            'inspection',
            'pending_approval',
            'approved',
            'assigned',
            'held',
          ].includes(shipment.status)
        "
        class="live-panel"
      >
        <summary class="cursor-pointer text-error font-semibold">
          Batalkan kiriman sebelum pengantaran
        </summary>
        <form
          class="grid gap-4 mt-4"
          @submit.prevent="confirmed && act('cancel')"
        >
          <label class="live-field"
            ><span>Alasan pembatalan</span
            ><textarea
              v-model="reason"
              name="reason"
              minlength="5"
              maxlength="4000"
              required
            /></label
          ><label class="flex items-center gap-3"
            ><input
              v-model="confirmed"
              name="confirm_cancel"
              type="checkbox"
              required
            />Saya memahami kiriman akan dibatalkan.</label
          ><button
            type="submit"
            class="live-button self-start text-error"
            :disabled="!confirmed || mutation.busy.value"
          >
            Batalkan kiriman ini
          </button>
        </form>
      </details>
    </template>
  </div>
</template>

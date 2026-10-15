<script setup lang="ts">
import { computed, onScopeDispose, ref, watch } from 'vue'
import { useApi, useMutation } from '../../services/useApi'
import {
  awbPath,
  type LiveShipment,
  type Response,
} from '../../services/models'
import ApiState from '../layout/ApiState.vue'
import ShipmentRecord from '../layout/ShipmentRecord.vue'
import CheckerExamChecklistModule from './CheckerExamChecklistModule.vue'
const props = defineProps<{ awb: string }>()
const record = useApi<Response<LiveShipment>>(() =>
  props.awb ? '/checker/shipments/' + awbPath(props.awb) : '',
)
const mutation = useMutation()
const packaging = ref(false),
  seal = ref(false),
  label = ref(false),
  temperature = ref<number | string>(''),
  note = ref(''),
  file = ref<File | null>(null),
  preview = ref(''),
  photoError = ref('')
const shipment = computed(() => record.data.value?.data)
function selectPhoto(event: Event) {
  const selected = (event.target as HTMLInputElement).files?.[0]
  if (preview.value) URL.revokeObjectURL(preview.value)
  preview.value = ''
  file.value = null
  photoError.value = ''
  if (!selected) return
  if (
    !['image/png', 'image/jpeg', 'image/webp'].includes(selected.type) ||
    selected.size > 5 * 1024 * 1024
  ) {
    photoError.value = 'Pilih JPG, PNG, atau WebP maksimal 5 MB.'
    return
  }
  file.value = selected
  preview.value = URL.createObjectURL(selected)
}
watch(
  () => props.awb,
  () => {
    packaging.value = false
    seal.value = false
    label.value = false
    temperature.value = ''
    note.value = ''
    file.value = null
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = ''
  },
)
onScopeDispose(() => {
  if (preview.value) URL.revokeObjectURL(preview.value)
})
async function submit() {
  if (!shipment.value || !file.value) return
  const data = new FormData()
  data.set('version', String(shipment.value.version))
  data.set('packaging', packaging.value ? '1' : '0')
  data.set('seal', seal.value ? '1' : '0')
  data.set('label', label.value ? '1' : '0')
  data.set('temperature', String(temperature.value))
  data.set('note', note.value)
  data.set('photo', file.value)
  if (
    await mutation.run(
      '/checker/shipments/' + awbPath(props.awb) + '/inspection',
      data,
    )
  )
    await record.reload()
}
</script>
<template>
  <div class="flex flex-col gap-5">
    <p v-if="!awb" class="live-panel">
      Pilih kiriman dari antrean sebelum melakukan pemeriksaan.
      <RouterLink to="/checker" class="text-primary underline"
        >Buka antrean</RouterLink
      >
    </p>
    <ApiState
      :loading="record.loading.value"
      :error="record.error.value || mutation.error.value"
      :notice="mutation.notice.value"
      @retry="record.reload"
    />
    <template v-if="shipment">
      <ShipmentRecord :shipment="shipment" />
      <form
        v-if="shipment.can_submit"
        class="grid lg:grid-cols-12 gap-6"
        @submit.prevent="submit"
      >
        <fieldset :disabled="mutation.busy.value" class="lg:col-span-7 min-w-0">
          <CheckerExamChecklistModule
            :minimum="shipment.temperature_min"
            :maximum="shipment.temperature_max"
            v-model:packaging-passed="packaging"
            v-model:seal-passed="seal"
            v-model:label-passed="label"
            v-model:temperature="temperature"
            v-model:inspection-note="note"
          />
        </fieldset>
        <section class="live-panel lg:col-span-5 flex flex-col gap-5">
          <div>
            <h2 class="text-xl font-semibold">Bukti pemeriksaan</h2>
            <p class="mt-2 text-on-surface-variant">
              Unggah foto yang baru diperiksa. Hasil lolos dihitung server dari
              checklist dan rentang suhu, bukan dari tombol persetujuan.
            </p>
          </div>
          <label class="live-field"
            ><span>Foto kemasan dan indikator suhu</span
            ><input
              name="photo"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              required
              :disabled="mutation.busy.value"
              @change="selectPhoto"
          /></label>
          <img
            v-if="preview"
            :src="preview"
            alt="Pratinjau foto yang akan dikirim"
            class="max-h-72 w-full object-contain rounded-xl"
          />
          <p v-if="photoError" role="alert" class="text-error">
            {{ photoError }}
          </p>
          <p>
            OCR belum terhubung. Catat suhu hasil pemeriksaan secara manual.
          </p>
          <button
            type="submit"
            class="live-button live-primary self-start"
            :disabled="
              mutation.busy.value ||
              !file ||
              note.trim().length < 5 ||
              temperature === ''
            "
          >
            {{
              mutation.busy.value
                ? 'Mengirim pemeriksaan…'
                : 'Kirim hasil ke Admin'
            }}
          </button>
        </section>
      </form>
      <section v-else class="live-panel">
        <h2 class="text-lg font-semibold">Pemeriksaan sudah dikirim</h2>
        <p class="mt-3">
          Hasil tidak dapat diubah. Tunggu keputusan Admin atau permintaan
          pemeriksaan ulang.
        </p>
        <RouterLink class="live-button mt-4" to="/checker/riwayat"
          >Lihat riwayat pemeriksaan</RouterLink
        >
      </section>
    </template>
  </div>
</template>

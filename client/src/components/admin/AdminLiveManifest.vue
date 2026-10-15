<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useApi, useMutation } from '../../services/useApi'
import type { Lookups, Response } from '../../services/models'
import ApiState from '../layout/ApiState.vue'
const emit = defineEmits<{ created: [awb: string] }>()
const lookups = useApi<Response<Lookups>>(() => '/admin/lookups')
const mutation = useMutation()
const initial = () => ({
  tracking_number: '',
  buyer_id: '',
  inspector_id: '',
  sender_name: '',
  recipient_name: '',
  recipient_phone: '',
  address: '',
  origin_city: '',
  destination_city: '',
  item_summary: '',
  package_count: 1,
  weight_kg: 1,
  temperature_profile: 'controlled',
  temperature_min: 2,
  temperature_max: 8,
  scheduled_at: '',
  handling_instructions: '',
  batch_number: '',
  package_type: '',
})
const form = reactive(initial()),
  validation = ref('')
const fields = [
  { key: 'tracking_number', label: 'Nomor resi', max: 100 },
  { key: 'sender_name', label: 'Nama pengirim', max: 255 },
  { key: 'recipient_name', label: 'Nama penerima', max: 255 },
  { key: 'recipient_phone', label: 'Telepon penerima', max: 25 },
  { key: 'origin_city', label: 'Kota asal', max: 255 },
  { key: 'destination_city', label: 'Kota tujuan', max: 255 },
  { key: 'item_summary', label: 'Isi kiriman', max: 255 },
] as const
async function submit() {
  validation.value = ''
  const date = new Date(form.scheduled_at)
  if (
    !Number.isFinite(date.getTime()) ||
    Number(form.temperature_max) <= Number(form.temperature_min)
  ) {
    validation.value = 'Periksa jadwal dan rentang suhu.'
    return
  }
  const result = await mutation.run('/admin/shipments', {
    ...form,
    tracking_number: form.tracking_number.trim().toUpperCase(),
    buyer_id: Number(form.buyer_id),
    inspector_id: Number(form.inspector_id),
    scheduled_at: date.toISOString(),
  })
  if (result) {
    const awb = form.tracking_number.trim().toUpperCase()
    Object.assign(form, initial())
    emit('created', awb)
  }
}
</script>
<template>
  <form class="live-panel flex flex-col gap-5" @submit.prevent="submit">
    <div>
      <h2 class="text-xl font-semibold">Daftarkan kiriman</h2>
      <p class="mt-2 text-on-surface-variant">
        Kiriman masuk ke antrean Checker sebelum disetujui dan ditugaskan.
      </p>
    </div>
    <ApiState
      :loading="lookups.loading.value"
      :error="lookups.error.value"
      @retry="lookups.reload"
    />
    <fieldset
      :disabled="mutation.busy.value"
      class="grid sm:grid-cols-2 gap-4 min-w-0"
    >
      <label v-for="field in fields" :key="field.key" class="live-field"
        ><span>{{ field.label }}</span
        ><input
          v-model="form[field.key]"
          :name="field.key"
          :type="field.key === 'recipient_phone' ? 'tel' : 'text'"
          :maxlength="field.max"
          required
      /></label>
      <label class="live-field"
        ><span>Akun penerima</span
        ><select v-model="form.buyer_id" name="buyer_id" required>
          <option disabled value="">Pilih penerima</option>
          <option
            v-for="buyer in lookups.data.value?.data.buyers"
            :key="buyer.id"
            :value="buyer.id"
          >
            {{ buyer.name }}
          </option>
        </select></label
      >
      <label class="live-field"
        ><span>Petugas pemeriksa</span
        ><select v-model="form.inspector_id" name="inspector_id" required>
          <option disabled value="">Pilih Checker</option>
          <option
            v-for="checker in lookups.data.value?.data.checkers"
            :key="checker.id"
            :value="checker.id"
          >
            {{ checker.name }}
          </option>
        </select></label
      >
      <label class="live-field"
        ><span>Jumlah koli</span
        ><input
          v-model.number="form.package_count"
          name="package_count"
          type="number"
          min="1"
          max="10000"
          step="1"
          required
      /></label>
      <label class="live-field"
        ><span>Berat (kg)</span
        ><input
          v-model.number="form.weight_kg"
          name="weight_kg"
          type="number"
          min="0.001"
          max="100000"
          step="0.001"
          required
      /></label>
      <label class="live-field"
        ><span>Profil suhu</span
        ><select v-model="form.temperature_profile" name="temperature_profile">
          <option value="controlled">Terkontrol</option>
          <option value="ambient">Ambient</option>
          <option value="deep_freeze">Beku</option>
        </select></label
      >
      <label class="live-field"
        ><span>Jadwal tiba (waktu lokal)</span
        ><input
          v-model="form.scheduled_at"
          name="scheduled_at"
          type="datetime-local"
          required
      /></label>
      <label class="live-field"
        ><span>Suhu minimum (°C)</span
        ><input
          v-model.number="form.temperature_min"
          name="temperature_min"
          type="number"
          min="-100"
          max="100"
          step="0.1"
          required
      /></label>
      <label class="live-field"
        ><span>Suhu maksimum (°C)</span
        ><input
          v-model.number="form.temperature_max"
          name="temperature_max"
          type="number"
          min="-100"
          max="100"
          step="0.1"
          required
      /></label>
      <label class="live-field"
        ><span>Nomor batch (opsional)</span
        ><input v-model="form.batch_number" name="batch_number" maxlength="255"
      /></label>
      <label class="live-field"
        ><span>Kemasan (opsional)</span
        ><input v-model="form.package_type" name="package_type" maxlength="255"
      /></label>
      <label class="live-field sm:col-span-2"
        ><span>Alamat lengkap</span
        ><textarea
          v-model="form.address"
          name="address"
          maxlength="2000"
          required
          rows="2"
        />
      </label>
      <label class="live-field sm:col-span-2"
        ><span>Petunjuk penanganan</span
        ><textarea
          v-model="form.handling_instructions"
          name="handling_instructions"
          maxlength="4000"
          required
          rows="2"
        />
      </label>
    </fieldset>
    <ApiState
      :error="validation || mutation.error.value"
      :notice="mutation.notice.value"
      @retry="lookups.reload"
    />
    <button
      type="submit"
      class="live-button live-primary self-start"
      :disabled="mutation.busy.value || !lookups.data.value"
    >
      {{ mutation.busy.value ? 'Menyimpan…' : 'Daftarkan kiriman' }}
    </button>
  </form>
</template>

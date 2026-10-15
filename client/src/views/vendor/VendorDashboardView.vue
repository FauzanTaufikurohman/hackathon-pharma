<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { currentUser } from '../../services/auth'
import { useApi } from '../../services/useApi'
import { query } from '../../services/api'
import type { LiveShipment, Response } from '../../services/models'
import ApiState from '../../components/layout/ApiState.vue'
import ShipmentRecord from '../../components/layout/ShipmentRecord.vue'
interface Dashboard {
  metrics: {
    active_shipments_count: number
    critical_count: number
    average_temperature_str: string
    available_reports_count: number
    incident_count: number
  }
  priority_shipment: LiveShipment | null
}
const router = useRouter(),
  search = ref(''),
  submitted = ref('')
const { data, loading, error, reload } = useApi<Response<Dashboard>>(
  () => '/vendor/dashboard' + query({ awb: submitted.value }),
)
const shipment = computed(() => data.value?.data.priority_shipment)
function resetSearch() {
  search.value = ''
  submitted.value = ''
}
</script>
<template>
  <div class="flex flex-col gap-6">
    <header>
      <p class="text-primary font-semibold">
        Selamat datang, {{ currentUser?.name }}
      </p>
      <h1 class="text-3xl font-semibold tracking-tight mt-2">
        Pusat pantau kiriman Anda
      </h1>
      <p class="mt-3 text-on-surface-variant">
        Periksa kesiapan penerimaan, ikuti perjalanan kiriman, dan simpan bukti
        serah terima.
      </p>
    </header>
    <form
      class="live-panel flex flex-col sm:flex-row gap-3 items-end"
      @submit.prevent="submitted = search.trim()"
    >
      <label class="live-field flex-1"
        ><span>Cari nomor resi</span
        ><input
          v-model="search"
          name="tracking_number"
          maxlength="100"
          placeholder="Masukkan nomor resi milik akun Anda" /></label
      ><button type="submit" class="live-button" :disabled="loading">
        Cari kiriman</button
      ><button type="button" class="live-button" @click="resetSearch">
        Reset
      </button>
    </form>
    <ApiState :loading="loading" :error="error" @retry="reload" />
    <div v-if="data" class="grid sm:grid-cols-3 gap-4">
      <section class="live-panel">
        <p>Kiriman aktif</p>
        <p class="text-3xl tabular-nums font-semibold mt-3">
          {{ data.data.metrics.active_shipments_count }}
        </p>
      </section>
      <section class="live-panel">
        <p>Kendala terbuka</p>
        <p class="text-3xl tabular-nums font-semibold mt-3 text-error">
          {{ data.data.metrics.incident_count }}
        </p>
      </section>
      <section class="live-panel">
        <p>Laporan tersedia</p>
        <p class="text-3xl tabular-nums font-semibold mt-3">
          {{ data.data.metrics.available_reports_count }}
        </p>
      </section>
    </div>
    <template v-if="shipment"
      ><ShipmentRecord :shipment="shipment" />
      <div class="flex flex-wrap gap-3">
        <button
          type="button"
          class="live-button live-primary"
          @click="
            router.push(
              '/vendor/monitor/' + encodeURIComponent(shipment.tracking_number),
            )
          "
        >
          Pantau kiriman ini →</button
        ><RouterLink to="/paket-saya" class="live-button"
          >Semua kiriman saya</RouterLink
        >
      </div></template
    >
    <p v-else-if="data && !loading" class="live-panel">
      Belum ada kiriman aktif untuk akun ini.
      <RouterLink class="text-primary underline" to="/paket-saya"
        >Lihat riwayat kiriman</RouterLink
      >.
    </p>
  </div>
</template>

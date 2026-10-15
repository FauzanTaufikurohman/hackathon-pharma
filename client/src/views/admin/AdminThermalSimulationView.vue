<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLiveShipment from '../../components/admin/AdminLiveShipment.vue'
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'

const route = useRoute()
const router = useRouter()
const refresh = ref(0)
const selected = computed(() =>
  typeof route.query.awb === 'string' ? route.query.awb : '',
)

function select(awb: string) {
  void router.replace({ query: { ...route.query, awb } })
}
</script>

<template>
  <div>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-6">
      <header>
        <p class="text-primary font-semibold">Simulator rantai dingin</p>
        <h1 class="text-3xl font-semibold tracking-tight mt-2">
          Simulasi suhu dan anomali
        </h1>
        <p class="mt-3 text-on-surface-variant">
          Pilih kiriman demo untuk mencatat penyimpangan sensor, melihat
          prediksi tren, dan menguji koreksi termal. Data simulasi tidak
          mengendalikan perangkat nyata atau membuktikan kondisi obat.
        </p>
      </header>
      <div class="grid lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-5 min-w-0">
          <ShipmentDirectory
            role="admin"
            selectable
            :refresh-key="refresh"
            @select="select"
          />
        </div>
        <div class="lg:col-span-7 min-w-0">
          <AdminLiveShipment
            v-if="selected"
            :key="selected"
            :awb="selected"
            mode="monitor"
            @changed="refresh++"
          />
          <p v-else class="live-panel">
            Pilih kiriman demo dalam perjalanan. Kontrol simulasi hanya akan
            aktif untuk kiriman yang memenuhi syarat.
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

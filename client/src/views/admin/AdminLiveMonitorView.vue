<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ShipmentDirectory from '../../components/layout/ShipmentDirectory.vue'
import AdminLiveShipment from '../../components/admin/AdminLiveShipment.vue'

const route = useRoute(),
  router = useRouter(),
  refresh = ref(0)
const selected = computed(() =>
  typeof route.query.awb === 'string' ? route.query.awb : '',
)
function select(awb: string) {
  void router.replace({ query: { ...route.query, awb } })
}
function created(awb: string) {
  refresh.value++
  select(awb)
}
</script>
<template>
  <div>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-6">
      <header>
        <h1 class="text-3xl font-semibold tracking-tight">
          Pemantauan dan kendala
        </h1>
        <p class="mt-3 text-on-surface-variant">
          Pilih kiriman untuk melihat kondisi dan tindakan yang tersedia pada
          tahapnya.
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
            Pilih resi di daftar untuk melanjutkan.
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

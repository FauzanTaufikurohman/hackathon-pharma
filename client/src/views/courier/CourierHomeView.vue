<script setup lang="ts">
import { currentUser } from '../../services/auth'
import { useApi } from '../../services/useApi'
import type { LiveTask, Response } from '../../services/models'
import ApiState from '../../components/layout/ApiState.vue'
import CourierMetricCards from '../../components/courier/CourierMetricCards.vue'
import { useRouter } from 'vue-router'
const router = useRouter()
interface Dashboard {
  metrics: {
    new_tasks_count: number
    on_route_count: number
    issues_hold_count: number
    completed_today_count: number
  }
  priority_task: LiveTask | null
}
const { data, loading, error, reload } = useApi<Response<Dashboard>>(
  () => '/courier/dashboard',
)
</script>
<template>
  <div class="flex flex-col gap-6">
    <header>
      <p class="text-primary font-semibold">
        Selamat bertugas, {{ currentUser?.name }}
      </p>
      <h1 class="text-3xl font-semibold tracking-tight mt-2">
        Tugas pengantaran hari ini
      </h1>
      <p class="mt-3 text-on-surface-variant">
        Tanggapi penugasan, periksa paket, lalu catat perjalanan dan serah
        terima.
      </p>
    </header>
    <ApiState
      :loading="loading"
      :error="error"
      @retry="reload"
    /><CourierMetricCards
      v-if="data"
      :metrics="data.data.metrics"
      @card-click="
        (type) =>
          router.push({
            path: '/courier/tugas-saya',
            query: {
              status:
                type === 'new'
                  ? 'assigned'
                  : type === 'completed'
                    ? 'delivered'
                    : type,
            },
          })
      "
    />
    <section v-if="data?.data.priority_task" class="live-panel">
      <p class="font-semibold text-primary">
        {{ data.data.priority_task.awb }}
      </p>
      <h2 class="text-xl font-semibold mt-2">
        {{ data.data.priority_task.item_title }}
      </h2>
      <p class="mt-3">
        {{ data.data.priority_task.destination_name }} ·
        {{ data.data.priority_task.status_label }}
      </p>
      <RouterLink
        :to="
          '/courier/tugas/' + encodeURIComponent(data.data.priority_task.awb)
        "
        class="live-button live-primary mt-5"
        >Buka tugas prioritas →</RouterLink
      >
    </section>
    <p v-else-if="!loading && !error" class="live-panel">
      Belum ada tugas aktif. Penugasan baru akan muncul setelah Admin
      menugaskannya kepada Anda.
    </p>
    <RouterLink to="/courier/tugas-saya" class="live-button self-start"
      >Lihat semua tugas</RouterLink
    >
  </div>
</template>

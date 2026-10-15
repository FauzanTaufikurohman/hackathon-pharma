<script setup lang="ts">
import { ref } from 'vue'
import { useApi } from '../../services/useApi'
import { query, type Page } from '../../services/api'
import { dateText, type Approval } from '../../services/models'
import CheckerNavbar from '../../components/checker/CheckerNavbar.vue'
import CheckerFooter from '../../components/checker/CheckerFooter.vue'
import ApiState from '../../components/layout/ApiState.vue'
import PrivateImage from '../../components/layout/PrivateImage.vue'
interface History {
  id: number
  awb: string
  packaging: boolean
  seal: boolean
  label: boolean
  temperature: number
  note: string
  passed: boolean
  submitted_at: string
  workflow_status: string
  approval: (Approval & { decision: string }) | null
}
const search = ref(''),
  submitted = ref(''),
  page = ref(1)
const { data, loading, error, reload } = useApi<Page<History>>(
  () =>
    '/checker/history' +
    query({ awb: submitted.value, page: page.value, per_page: 20 }),
)
function submitSearch() {
  submitted.value = search.value.trim()
  page.value = 1
}
</script>
<template>
  <div>
    <CheckerNavbar :active-tab="3" />
    <main
      class="max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-10 flex flex-col gap-6"
    >
      <header>
        <h1 class="text-3xl font-semibold tracking-tight">
          Riwayat pemeriksaan
        </h1>
        <p class="mt-3 text-on-surface-variant">
          Hasil yang sudah dikirim dan keputusan Admin untuk pemeriksaan
          tersebut.
        </p>
      </header>
      <form class="flex gap-3 items-end" @submit.prevent="submitSearch">
        <label class="live-field flex-1"
          ><span>Cari nomor resi</span
          ><input v-model="search" name="awb" maxlength="100" /></label
        ><button type="submit" class="live-button">Cari</button>
      </form>
      <ApiState :loading="loading" :error="error" @retry="reload" />
      <p v-if="data && !data.data.length" class="live-panel">
        Belum ada pemeriksaan yang sesuai.
      </p>
      <article v-for="item in data?.data" :key="item.id" class="live-panel">
        <h2 class="text-xl font-semibold">{{ item.awb }}</h2>
        <p class="mt-3">
          {{ dateText(item.submitted_at) }} ·
          {{ item.passed ? 'Memenuhi pemeriksaan' : 'Ada temuan pemeriksaan' }}
        </p>
        <p class="mt-2">{{ item.temperature }} °C · {{ item.note }}</p>
        <p class="mt-3 font-semibold text-primary">
          {{
            item.approval
              ? 'Keputusan Admin: ' + item.approval.decision
              : 'Belum ada keputusan untuk pemeriksaan ini'
          }}
        </p>
        <p v-if="item.approval" class="mt-2">{{ item.approval.reason }}</p>
        <details class="mt-4">
          <summary class="cursor-pointer py-2">
            Lihat foto pemeriksaan #{{ item.id }}
          </summary>
          <PrivateImage
            :path="'/checker/inspections/' + item.id + '/photo'"
            alt="Foto pemeriksaan tersimpan"
          />
        </details>
      </article>
      <nav
        v-if="data"
        class="flex gap-3 items-center"
        aria-label="Halaman riwayat"
      >
        <button
          type="button"
          class="live-button"
          :disabled="page <= 1 || loading"
          @click="page--"
        >
          Sebelumnya
        </button>
        <p>
          {{ page }} / {{ data.meta.last_page }} ·
          {{ data.meta.total }} pemeriksaan
        </p>
        <button
          type="button"
          class="live-button"
          :disabled="page >= data.meta.last_page || loading"
          @click="page++"
        >
          Berikutnya
        </button>
      </nav>
    </main>
    <CheckerFooter />
  </div>
</template>

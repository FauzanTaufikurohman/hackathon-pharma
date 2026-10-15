<script setup lang="ts">
import { computed, ref } from 'vue'
import AdminNavbar from '../../components/admin/AdminNavbar.vue'
import AdminFooter from '../../components/admin/AdminFooter.vue'
import ApiState from '../../components/layout/ApiState.vue'
import { useApi } from '../../services/useApi'
import { download, errorText, query, send, type Page } from '../../services/api'
import {
  dateText,
  type Report,
  type EventRecord,
  type Response,
} from '../../services/models'
const date = ref(
  new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Jakarta',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date()),
)
const reportPage = ref(1),
  eventPage = ref(1),
  messagePage = ref(1),
  awb = ref(''),
  filterAwb = ref(''),
  busy = ref(false),
  notice = ref(''),
  actionError = ref('')
interface Paginator<T> {
  data: T[]
  total: number
  current_page: number
  last_page: number
}
interface Daily {
  date: string
  created_shipments: number
  closed_shipments: number
  published_reports: number
  new_incidents: number
}
interface Message {
  id: number
  subject: string
  body: string
  status: string
  attempts: number
  error_code: string | null
  sent_at: string | null
}
const daily = useApi<Response<Daily>>(
  () => '/admin/reports/daily' + query({ date: date.value }),
)
const reports = useApi<Paginator<Report>>(
  () =>
    '/admin/reports' + query({ page: reportPage.value, awb: filterAwb.value }),
)
const events = useApi<Paginator<EventRecord>>(
  () =>
    '/admin/events' + query({ page: eventPage.value, awb: filterAwb.value }),
)
const notifications = useApi<{
  gateway: { configured: boolean; enabled: boolean }
  messages: Paginator<Message>
}>(() => '/admin/notifications' + query({ page: messagePage.value }))
const gateway = computed(() => notifications.data.value?.gateway)
async function reload() {
  await Promise.all([
    daily.reload(),
    reports.reload(),
    events.reload(),
    notifications.reload(),
  ])
}
async function pdf(report: Report) {
  actionError.value = ''
  try {
    await download(
      '/admin/reports/' + report.id + '/download',
      'laporan-' + report.id + '.pdf',
    )
  } catch (cause) {
    actionError.value = errorText(cause)
  }
}
async function distribute(items: Report[]) {
  if (
    busy.value ||
    !items.length ||
    !confirm(
      'Antrekan ' +
        items.length +
        ' laporan ini untuk penerima yang terkait? Pesan belum tentu terkirim jika Telegram belum aktif.',
    )
  )
    return
  busy.value = true
  notice.value = ''
  actionError.value = ''
  let queued = 0
  try {
    for (const item of items) {
      await send('/admin/reports/' + item.id + '/distribute', {})
      queued++
    }
    notice.value =
      queued +
      ' laporan masuk antrean Telegram. Ini belum merupakan bukti pesan terkirim.'
  } catch (cause) {
    actionError.value =
      queued +
      ' laporan sudah diantrekan; sisanya belum dikonfirmasi. ' +
      errorText(cause)
  } finally {
    busy.value = false
    await notifications.reload()
  }
}
async function retry(message: Message) {
  if (busy.value) return
  const uncertain = message.status === 'uncertain'
  if (
    uncertain &&
    !confirm(
      'Pesan mungkin sudah sampai. Sudah cek HP penerima dan tetap ingin mengirim ulang? Risiko pesan ganda.',
    )
  )
    return
  busy.value = true
  actionError.value = ''
  notice.value = ''
  try {
    await send('/admin/notifications/' + message.id + '/retry', {
      confirm_possible_duplicate: uncertain,
    })
    notice.value = 'Pesan masuk antrean ulang. Tunggu hasil worker.'
    await notifications.reload()
  } catch (cause) {
    actionError.value = errorText(cause)
  } finally {
    busy.value = false
  }
}
function submitSearch() {
  filterAwb.value = awb.value.trim()
  reportPage.value = 1
  eventPage.value = 1
}
</script>
<template>
  <div>
    <AdminNavbar active-tab="reports" />
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-6">
      <header>
        <h1 class="text-3xl font-semibold tracking-tight">Laporan dan audit</h1>
        <p class="mt-3 text-on-surface-variant">
          Unduh laporan tersimpan, periksa riwayat kiriman, dan pantau status
          notifikasi.
        </p>
      </header>
      <ApiState :error="actionError" :notice="notice" @retry="reload" />
      <section class="live-panel">
        <div class="flex flex-wrap items-end justify-between gap-4">
          <h2 class="text-xl font-semibold">Ringkasan harian (WIB)</h2>
          <label class="live-field"
            ><span>Tanggal</span
            ><input v-model="date" name="date" type="date" required
          /></label>
        </div>
        <ApiState
          :loading="daily.loading.value"
          :error="daily.error.value"
          @retry="daily.reload"
        />
        <dl
          v-if="daily.data.value"
          class="grid grid-cols-2 lg:grid-cols-4 gap-5 mt-5"
        >
          <div>
            <dt>Kiriman dibuat</dt>
            <dd>{{ daily.data.value.data.created_shipments }}</dd>
          </div>
          <div>
            <dt>Kiriman ditutup</dt>
            <dd>{{ daily.data.value.data.closed_shipments }}</dd>
          </div>
          <div>
            <dt>Laporan terbit</dt>
            <dd>{{ daily.data.value.data.published_reports }}</dd>
          </div>
          <div>
            <dt>Kendala baru</dt>
            <dd>{{ daily.data.value.data.new_incidents }}</dd>
          </div>
        </dl>
      </section>
      <form
        class="flex flex-wrap gap-3 items-end"
        @submit.prevent="submitSearch"
      >
        <label class="live-field flex-1"
          ><span>Saring laporan dan audit berdasarkan resi</span
          ><input
            v-model="awb"
            name="awb"
            maxlength="100"
            placeholder="Kosongkan untuk semua resi" /></label
        ><button type="submit" class="live-button">Terapkan</button
        ><button type="button" class="live-button" @click="reload">
          Muat ulang
        </button>
      </form>
      <section class="live-panel">
        <div class="flex flex-wrap justify-between gap-3">
          <h2 class="text-xl font-semibold">Laporan pengiriman</h2>
          <button
            type="button"
            class="live-button"
            :disabled="busy || !reports.data.value?.data.length"
            @click="distribute(reports.data.value?.data || [])"
          >
            Antrekan laporan di halaman ini
          </button>
        </div>
        <ApiState
          :loading="reports.loading.value"
          :error="reports.error.value"
          @retry="reports.reload"
        />
        <p
          v-if="reports.data.value && !reports.data.value.data.length"
          class="py-5"
        >
          Belum ada laporan untuk filter ini.
        </p>
        <article
          v-for="report in reports.data.value?.data"
          :key="report.id"
          class="py-5 border-b border-outline/15"
        >
          <p class="font-semibold">
            #{{ report.id }} · {{ report.title }} · Revisi {{ report.revision }}
          </p>
          <p class="mt-2">
            {{ dateText(report.published_at) }}
            {{ report.is_demo ? '· Data contoh' : '' }}
          </p>
          <p class="mt-2 break-all text-on-surface-variant">
            SHA-256 {{ report.sha256 }}
          </p>
          <div class="flex flex-wrap gap-3 mt-4">
            <button type="button" class="live-button" @click="pdf(report)">
              Unduh PDF</button
            ><button
              type="button"
              class="live-button"
              :disabled="busy"
              @click="distribute([report])"
            >
              Antrekan notifikasi laporan
            </button>
          </div>
        </article>
        <nav
          v-if="reports.data.value"
          class="flex gap-3 items-center mt-4"
          aria-label="Halaman laporan"
        >
          <button
            type="button"
            class="live-button"
            :disabled="reportPage <= 1 || reports.loading.value"
            @click="reportPage--"
          >
            Sebelumnya
          </button>
          <p>{{ reportPage }} / {{ reports.data.value.last_page }}</p>
          <button
            type="button"
            class="live-button"
            :disabled="
              reportPage >= reports.data.value.last_page ||
              reports.loading.value
            "
            @click="reportPage++"
          >
            Berikutnya
          </button>
        </nav>
      </section>
      <section class="live-panel">
        <h2 class="text-xl font-semibold">Riwayat aktivitas</h2>
        <ApiState
          :loading="events.loading.value"
          :error="events.error.value"
          @retry="events.reload"
        />
        <p
          v-if="events.data.value && !events.data.value.data.length"
          class="mt-4"
        >
          Belum ada aktivitas yang sesuai.
        </p>
        <ol role="list" class="divide-y divide-outline/15 mt-4">
          <li
            v-for="event in events.data.value?.data"
            :key="event.id"
            class="py-4"
          >
            <p class="font-semibold">{{ event.title }}</p>
            <p class="mt-1">
              {{ dateText(event.created_at) }} · {{ event.from_status }} →
              {{ event.to_status }}
            </p>
            <p v-if="event.reason" class="mt-1">{{ event.reason }}</p>
          </li>
        </ol>
        <nav
          v-if="events.data.value"
          class="flex gap-3 items-center mt-4"
          aria-label="Halaman audit"
        >
          <button
            type="button"
            class="live-button"
            :disabled="eventPage <= 1 || events.loading.value"
            @click="eventPage--"
          >
            Sebelumnya
          </button>
          <p>{{ eventPage }} / {{ events.data.value.last_page }}</p>
          <button
            type="button"
            class="live-button"
            :disabled="
              eventPage >= events.data.value.last_page || events.loading.value
            "
            @click="eventPage++"
          >
            Berikutnya
          </button>
        </nav>
      </section>
      <section class="live-panel">
        <h2 class="text-xl font-semibold">Antrean Telegram</h2>
        <p class="mt-3">
          {{
            gateway?.configured && gateway.enabled
              ? 'Gateway diaktifkan. Periksa status tiap pesan.'
              : 'Gateway belum diaktifkan atau token belum dipasang. Pesan tidak diklaim terkirim.'
          }}
        </p>
        <ApiState
          :loading="notifications.loading.value"
          :error="notifications.error.value"
          @retry="notifications.reload"
        />
        <p
          v-if="
            notifications.data.value &&
            !notifications.data.value.messages.data.length
          "
          class="mt-4"
        >
          Belum ada pesan.
        </p>
        <article
          v-for="message in notifications.data.value?.messages.data"
          :key="message.id"
          class="py-4 border-b border-outline/15"
        >
          <p class="font-semibold">
            #{{ message.id }} · {{ message.subject }} · {{ message.status }}
          </p>
          <p class="mt-2 break-words">{{ message.body }}</p>
          <p class="mt-2">
            Percobaan {{ message.attempts }}
            <span v-if="message.error_code">· {{ message.error_code }}</span
            ><span v-if="message.sent_at">
              · ACK provider {{ dateText(message.sent_at) }}</span
            >
          </p>
          <button
            v-if="
              ['failed', 'unconfigured', 'uncertain'].includes(message.status)
            "
            type="button"
            class="live-button mt-3"
            :disabled="busy"
            @click="retry(message)"
          >
            Antrekan ulang pesan
          </button>
        </article>
        <nav
          v-if="notifications.data.value"
          class="flex gap-3 items-center mt-4"
          aria-label="Halaman notifikasi"
        >
          <button
            type="button"
            class="live-button"
            :disabled="messagePage <= 1 || notifications.loading.value"
            @click="messagePage--"
          >
            Sebelumnya
          </button>
          <p>
            {{ messagePage }} /
            {{ notifications.data.value.messages.last_page }}
          </p>
          <button
            type="button"
            class="live-button"
            :disabled="
              messagePage >= notifications.data.value.messages.last_page ||
              notifications.loading.value
            "
            @click="messagePage++"
          >
            Berikutnya
          </button>
        </nav>
      </section>
      <p class="text-on-surface-variant">
        PDF dan checksum adalah catatan operasional aplikasi, bukan sertifikasi
        BPOM atau tanda tangan digital tersertifikasi. Status sent berarti
        provider menerima pesan, bukan bukti penerima telah membacanya.
      </p>
    </main>
    <AdminFooter />
  </div>
</template>

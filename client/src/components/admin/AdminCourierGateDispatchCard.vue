<script setup lang="ts">
import { ref } from 'vue'
import type { AdminAvailableCourier } from '../../types/shipment'

const props = defineProps<{
  couriers: AdminAvailableCourier[]
  decisionStatus: 'pending' | 'approved' | 'held'
  selectedCourierId: string
  isDispatching: boolean
  isDispatched: boolean
}>()

const emit = defineEmits<{
  (e: 'select-courier', courierId: string): void
  (e: 'dispatch', params: { courierId: string; notifyBot: boolean; enableBeacon: boolean }): void
}>()

const notifyBot = ref(true)
const enableBeacon = ref(true)

function handleDispatch() {
  if (props.decisionStatus === 'held' || props.isDispatching) return
  emit('dispatch', {
    courierId: props.selectedCourierId,
    notifyBot: notifyBot.value,
    enableBeacon: enableBeacon.value,
  })
}
</script>

<template>
  <div
    id="courier-card"
    :class="[
      'bg-surface-container-lowest rounded-xl shadow-sm p-6 flex flex-col gap-5 relative overflow-hidden transition-all duration-300 border border-surface-container-low',
      decisionStatus === 'held' ? 'opacity-60 pointer-events-none grayscale-[0.2]' : '',
    ]"
  >
    <!-- Card Header -->
    <div class="flex items-center justify-between gap-2 pb-4 bg-gradient-to-r from-surface-container-lowest to-surface-container-low/40 -mx-6 -mt-6 p-6 rounded-t-xl border-b border-surface-container-low">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary shrink-0">
          <span class="material-symbols-outlined">local_shipping</span>
        </div>
        <div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">2. Alokasi Armada &amp; Penugasan Kurir Satria</h2>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Disposisi kurir farmasi berlisensi • Tabel: assignments</p>
        </div>
      </div>

      <!-- Gate Badge -->
      <span
        v-if="decisionStatus === 'approved'"
        id="gate-badge"
        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold shrink-0"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
        Gerbang Terbuka • Siap Ditugaskan
      </span>
      <span
        v-else-if="decisionStatus === 'held'"
        id="gate-badge"
        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-error-container text-error font-label-sm text-label-sm font-semibold shrink-0"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
        Gerbang Terkunci (Kargo Ditahan)
      </span>
      <span
        v-else
        id="gate-badge"
        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-900 font-label-sm text-label-sm font-semibold shrink-0"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
        Menunggu Otorisasi Gerbang 1
      </span>
    </div>

    <!-- Gate Status Notice -->
    <div
      v-if="decisionStatus === 'approved'"
      id="gate-notice"
      class="bg-emerald-50 rounded-xl p-4 flex items-start gap-3 border border-emerald-100"
    >
      <div class="bg-emerald-100 text-emerald-800 p-1.5 rounded-lg shrink-0 mt-0.5">
        <span class="material-symbols-outlined text-lg">lock_open</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-md text-label-md text-emerald-950 font-bold">✓ Kargo telah disetujui (Status: approved)</span>
        <p class="font-body-sm text-body-sm text-emerald-900 mt-0.5 leading-relaxed">
          Sistem membuka otorisasi pemilihan kurir Satria Pharma Cold-Chain. Pilih kurir dengan spesifikasi payload pendingin yang sesuai dengan stabilitas vaksin.
        </p>
      </div>
    </div>
    <div
      v-else-if="decisionStatus === 'held'"
      id="gate-notice"
      class="bg-error-container/60 rounded-xl p-4 flex items-start gap-3 border border-error/20"
    >
      <div class="bg-error-container text-error p-1.5 rounded-lg shrink-0 mt-0.5">
        <span class="material-symbols-outlined text-lg">lock</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-md text-label-md text-error font-bold">× Kargo Ditahan (Status: held)</span>
        <p class="font-body-sm text-body-sm text-on-surface mt-0.5 leading-relaxed">
          Otorisasi dispatch kurir dinonaktifkan sementara karena kargo ditahan untuk investigasi mutu QA.
        </p>
      </div>
    </div>
    <div
      v-else
      id="gate-notice"
      class="bg-amber-50 rounded-xl p-4 flex items-start gap-3 border border-amber-200/60"
    >
      <div class="bg-amber-100 text-amber-800 p-1.5 rounded-lg shrink-0 mt-0.5">
        <span class="material-symbols-outlined text-lg">pending</span>
      </div>
      <div class="flex flex-col">
        <span class="font-label-md text-label-md text-amber-950 font-bold">Menunggu Otorisasi Admin (Status: pending_approval)</span>
        <p class="font-body-sm text-body-sm text-amber-900 mt-0.5 leading-relaxed">
          Silakan tinjau dan klik "Setujui Pengiriman" pada Gerbang 1 untuk mengaktifkan disposisi kurir.
        </p>
      </div>
    </div>

    <!-- Courier Selector Radio Cards (assignments.courier_id) -->
    <div class="flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-bold">
          Kurir Tersedia di Hub Bandung (Cold-Chain Certified):
        </span>
        <span class="font-label-sm text-label-sm text-secondary font-semibold">2 Unit Standby</span>
      </div>

      <!-- Courier Options -->
      <label
        v-for="courier in couriers"
        :key="courier.id"
        class="relative block cursor-pointer group"
      >
        <input
          type="radio"
          name="courier_selection"
          :value="courier.id"
          :checked="selectedCourierId === courier.id"
          @change="emit('select-courier', courier.id)"
          class="peer sr-only"
        />
        <div
          :class="[
            'p-4 rounded-xl shadow-xs transition-all flex flex-col gap-3 border',
            selectedCourierId === courier.id
              ? 'bg-primary-fixed/20 border-primary shadow-xs'
              : 'bg-surface-container-low border-surface-container hover:bg-surface-container',
          ]"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0',
                  selectedCourierId === courier.id ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant',
                ]"
              >
                {{ courier.initials }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-headline-sm text-headline-sm text-on-surface font-bold">{{ courier.name }}</span>
                  <span class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm px-2 py-0.5 rounded font-semibold">
                    ID: {{ courier.code }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5 text-on-surface-variant font-body-sm text-body-sm mt-0.5">
                  <span
                    :class="[
                      'material-symbols-outlined text-sm',
                      selectedCourierId === courier.id ? 'text-primary' : 'text-on-surface-variant',
                    ]"
                  >
                    {{ courier.vehicle_icon }}
                  </span>
                  <span class="font-semibold text-on-surface">{{ courier.vehicle_type }}</span>
                  <span>(Plat: {{ courier.plate_number }})</span>
                </div>
              </div>
            </div>

            <!-- Radio visual indicator -->
            <div
              :class="[
                'w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-1 transition-all',
                selectedCourierId === courier.id
                  ? 'bg-primary text-white shadow-xs'
                  : 'bg-surface-container-highest text-transparent border border-outline/30',
              ]"
            >
              <span class="material-symbols-outlined text-sm font-bold">check</span>
            </div>
          </div>

          <!-- Courier Metadata Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 font-label-sm text-label-sm">
            <div class="bg-surface-container-lowest/80 rounded-lg p-2 border border-surface-container-low">
              <span class="text-on-surface-variant block mb-0.5">Kesiapan Chiller</span>
              <span class="font-bold text-secondary flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">ac_unit</span>
                {{ courier.battery_pct ? `${courier.chiller_temp.toFixed(1)}°C • Batt ${courier.battery_pct}%` : `Chilled ${courier.chiller_temp.toFixed(1)}°C` }}
              </span>
            </div>
            <div class="bg-surface-container-lowest/80 rounded-lg p-2 border border-surface-container-low">
              <span class="text-on-surface-variant block mb-0.5">Beban Kerja</span>
              <span
                :class="[
                  'font-bold flex items-center gap-1',
                  courier.active_tasks === 0 ? 'text-on-surface' : 'text-amber-800',
                ]"
              >
                <span
                  v-if="courier.active_tasks === 0"
                  class="material-symbols-outlined text-xs text-secondary"
                >
                  flash_on
                </span>
                <span
                  v-else
                  class="material-symbols-outlined text-xs text-amber-600"
                >
                  timelapse
                </span>
                {{ courier.active_tasks_desc }}
              </span>
            </div>
            <div class="bg-surface-container-lowest/80 rounded-lg p-2 border border-surface-container-low">
              <span class="text-on-surface-variant block mb-0.5">Kredensial</span>
              <span class="font-bold text-amber-700 flex items-center gap-1">
                <span class="material-symbols-outlined text-xs text-amber-500 fill-1">star</span>
                {{ courier.credential_cpob }}
              </span>
            </div>
          </div>
        </div>
      </label>
    </div>

    <!-- Assignment Dispatch Options -->
    <div class="bg-surface-container-low rounded-xl p-4 flex flex-col gap-2.5 border border-surface-container">
      <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5 font-bold">
        Parameter Dispatch Otomatis:
      </span>
      <label class="flex items-start gap-2.5 cursor-pointer">
        <input
          v-model="notifyBot"
          type="checkbox"
          class="mt-1 w-4 h-4 rounded text-primary focus:ring-primary/20 cursor-pointer"
        />
        <span class="font-body-sm text-body-sm text-on-surface">
          Kirim notifikasi otomatis instan via <strong>WhatsApp Bot &amp; Telegram Dispatcher</strong> ke kurir terpilih
        </span>
      </label>
      <label class="flex items-start gap-2.5 cursor-pointer">
        <input
          v-model="enableBeacon"
          type="checkbox"
          class="mt-1 w-4 h-4 rounded text-primary focus:ring-primary/20 cursor-pointer"
        />
        <span class="font-body-sm text-body-sm text-on-surface">
          Aktifkan beacon telemetri IoT 30-detik rute publik pelanggan (Hospital Recipient Gateway)
        </span>
      </label>
    </div>

    <!-- Assignment Status Monitor (assignments.status) -->
    <div class="bg-surface-container rounded-xl p-4 flex flex-col gap-2 border border-surface-container-high/60">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <span class="font-label-md text-label-md text-on-surface font-semibold">Monitor Status Penugasan:</span>
        <span
          v-if="!isDispatched"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-lowest text-on-surface-variant font-label-sm text-label-sm shadow-2xs font-semibold"
        >
          <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
          Menunggu Konfirmasi Kurir (pending)
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 font-label-sm text-label-sm shadow-2xs font-bold"
        >
          <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
          Tugas Diterima &amp; Terdisposisi (assigned)
        </span>
      </div>

      <!-- In case of rejection notification banner -->
      <div class="bg-surface-container-lowest rounded-lg p-3 flex items-start gap-2.5 text-on-surface-variant font-body-sm text-body-sm border border-surface-container-low">
        <span class="material-symbols-outlined text-amber-600 text-lg shrink-0 mt-0.5">info</span>
        <p>
          Jika kurir menolak (<code class="font-mono text-xs text-error font-semibold">rejected</code>), alasan penolakan kurir akan muncul di sini dan Admin dapat mengalokasikan kurir pengganti dalam 1 klik.
        </p>
      </div>
    </div>

    <!-- Primary CTA Button (Tugaskan Kurir) -->
    <button
      type="button"
      id="btn-dispatch"
      :disabled="decisionStatus === 'held' || isDispatching"
      @click="handleDispatch"
      :class="[
        'w-full font-label-lg text-label-lg py-3.5 px-6 rounded-xl shadow-md flex items-center justify-center gap-2 transition-all active:scale-[0.99] cursor-pointer',
        isDispatched
          ? 'bg-secondary text-on-secondary hover:bg-secondary/90'
          : 'bg-primary-container hover:bg-primary-container/90 text-on-primary',
        decisionStatus === 'held' || isDispatching ? 'opacity-50 cursor-not-allowed' : '',
      ]"
    >
      <template v-if="isDispatching">
        <span class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
        <span>Memproses Dispatch &amp; Notifikasi...</span>
      </template>
      <template v-else-if="isDispatched">
        <span class="material-symbols-outlined text-xl">task_alt</span>
        <span>Tugas Terkirim ke Bambang Suryono (WA/TG Sent)</span>
      </template>
      <template v-else>
        <span class="material-symbols-outlined text-xl">send_and_archive</span>
        <span>Tugaskan Kurir &amp; Kirim Notifikasi WA/Telegram (status → assigned)</span>
      </template>
    </button>
  </div>
</template>

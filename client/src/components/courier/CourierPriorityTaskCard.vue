<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

interface Props {
  awb?: string
  itemTitle?: string
  batchNumber?: string
  biofarmaId?: string
  originHub?: string
  originBay?: string
  destinationName?: string
  destinationAddress?: string
  deadlineTime?: string
  travelDurationMin?: number
  sealNumber?: string
  sealStatus?: string
  probeSensorId?: string
  probeTemp?: number
  probeStatusText?: string
  pharmacistInCharge?: string
  statusLabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  awb: 'PHA-260104',
  itemTitle: 'Vaksin Meningococcal (2 Koli / 400 Dosis)',
  batchNumber: '#MCV-2026-X12',
  biofarmaId: '#BF-901',
  originHub: 'Hub Gudang Cawang, Jakarta Timur',
  originBay: 'Dermaga Dingin Staging Bay 3B',
  destinationName: 'RS Medika Sehat Jakarta',
  destinationAddress: 'Instalasi Farmasi Gd. B Lt. 1, Jl. Raya Salemba No. 12',
  deadlineTime: 'Maks. 15:30 WIB',
  travelDurationMin: 24,
  sealNumber: 'SL-8891',
  sealStatus: 'Status: Terkunci Rapat',
  probeSensorId: 'IoT Probe ST-300',
  probeTemp: 4.2,
  probeStatusText: '(Optimal)',
  pharmacistInCharge: 'apt. Nadia Rahma, S.Farm (SIP: 198902-RSMS)',
  statusLabel: 'Menunggu Jawaban Satria',
})

const router = useRouter()
const isCopied = ref(false)

const emit = defineEmits<{
  (e: 'copy-awb', awb: string): void
  (e: 'process-task', awb: string): void
  (e: 'view-all-tasks'): void
}>()

function handleCopy() {
  navigator.clipboard.writeText(props.awb)
  isCopied.value = true
  emit('copy-awb', props.awb)
  setTimeout(() => {
    isCopied.value = false
  }, 2000)
}

function handleProcessTask() {
  emit('process-task', props.awb)
  router.push('/courier/tugas-saya')
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <!-- Section Title & Priority Pill -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-container opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary-container"></span>
        </span>
        <h2 class="font-headline-sm text-on-surface font-bold">
          Tugas Prioritas Memerlukan Tindakan
        </h2>
      </div>
      <span class="font-label-sm text-outline uppercase tracking-wider font-bold">
        Prioritas Statim / Segera
      </span>
    </div>

    <!-- Spotlight Priority Task Card -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col gap-6 relative border border-surface-container/60 overflow-hidden">
      <!-- Decorative corner shape -->
      <div class="absolute top-0 right-0 w-32 h-32 bg-primary-fixed/20 rounded-bl-full pointer-events-none"></div>

      <!-- Task Header Strip -->
      <div class="flex flex-wrap items-center justify-between gap-3 pb-4 bg-surface-container-low/40 p-4 rounded-xl border border-surface-container/40">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-primary-container flex items-center justify-center text-on-primary shadow-xs">
            <span class="material-symbols-outlined text-[22px]">medical_services</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-label-lg text-on-surface font-bold">AWB: {{ awb }}</span>
              <button
                type="button"
                @click="handleCopy"
                class="text-outline hover:text-primary-container transition-colors cursor-pointer"
                :title="isCopied ? 'Tersalin!' : 'Salin nomor resi'"
              >
                <span class="material-symbols-outlined text-[16px]">
                  {{ isCopied ? 'check' : 'content_copy' }}
                </span>
              </button>
            </div>
            <span class="font-body-sm text-on-surface-variant">Kategori: Vaksin &amp; Biologis Bersertifikat</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 z-10">
          <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-surface-container-highest text-secondary font-label-sm font-bold shadow-2xs">
            <span class="material-symbols-outlined text-[16px]">ac_unit</span>
            Controlled Chilled (2.0°C – 8.0°C)
          </span>
          <span class="px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm uppercase font-bold shadow-2xs">
            {{ statusLabel }}
          </span>
        </div>
      </div>

      <!-- Cargo & Delivery Pathway Details (2 Columns) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left Detail: Muatan & Rute -->
        <div class="flex flex-col gap-4">
          <div class="flex flex-col gap-1">
            <span class="font-label-sm text-outline uppercase font-bold tracking-wider">Deskripsi Muatan Farmasi</span>
            <p class="font-headline-sm text-on-surface font-bold">{{ itemTitle }}</p>
            <p class="font-body-sm text-on-surface-variant">Batch {{ batchNumber }} • Bio Farma ID {{ biofarmaId }}</p>
          </div>

          <!-- Visual Route Path -->
          <div class="flex flex-col gap-3 p-3.5 bg-surface-container-low rounded-xl border border-surface-container/60">
            <!-- Pickup Point -->
            <div class="flex items-start gap-3">
              <span class="material-symbols-outlined text-[18px] text-primary-container mt-0.5">trip_origin</span>
              <div class="flex flex-col">
                <span class="font-label-sm text-outline font-semibold">Lokasi Penjemputan (Origin Hub)</span>
                <span class="font-label-md text-on-surface font-bold">{{ originHub }}</span>
                <span class="font-body-sm text-on-surface-variant">{{ originBay }}</span>
              </div>
            </div>

            <!-- Dashed separator -->
            <div class="ml-2 pl-3 border-l-2 border-dashed border-outline-variant py-0.5"></div>

            <!-- Destination Point -->
            <div class="flex items-start gap-3">
              <span class="material-symbols-outlined text-[18px] text-secondary mt-0.5">location_on</span>
              <div class="flex flex-col">
                <span class="font-label-sm text-outline font-semibold">Lokasi Tujuan (Faskes Penerima)</span>
                <span class="font-label-md text-on-surface font-bold">{{ destinationName }}</span>
                <span class="font-body-sm text-on-surface-variant">{{ destinationAddress }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Detail: Jadwal & Telemetri Sensor -->
        <div class="flex flex-col gap-4 justify-between">
          <div class="grid grid-cols-2 gap-3">
            <!-- Batas Waktu POD -->
            <div class="p-3 bg-surface-container-low rounded-xl flex flex-col gap-1 border border-surface-container/60">
              <span class="font-label-sm text-outline flex items-center gap-1 font-semibold">
                <span class="material-symbols-outlined text-[14px]">schedule</span>
                Batas Waktu POD
              </span>
              <span class="font-label-md text-on-surface font-bold">{{ deadlineTime }}</span>
              <span class="font-body-sm text-primary-container font-semibold">Est. Tempuh: {{ travelDurationMin }} Menit</span>
            </div>

            <!-- Segel Tamper-Evident -->
            <div class="p-3 bg-surface-container-low rounded-xl flex flex-col gap-1 border border-surface-container/60">
              <span class="font-label-sm text-outline flex items-center gap-1 font-semibold">
                <span class="material-symbols-outlined text-[14px]">lock</span>
                Segel Tamper-Evident
              </span>
              <span class="font-label-md text-on-surface font-mono font-bold">{{ sealNumber }}</span>
              <span class="font-body-sm text-secondary font-semibold">{{ sealStatus }}</span>
            </div>
          </div>

          <!-- Pre-dispatch Telemetry Bar Widget -->
          <div class="p-4 bg-surface-container-low rounded-xl flex flex-col gap-2 border border-surface-container/60">
            <div class="flex items-center justify-between">
              <span class="font-label-sm text-outline flex items-center gap-1.5 font-semibold">
                <span class="material-symbols-outlined text-[16px] text-secondary">sensors</span>
                {{ probeSensorId }}
              </span>
              <span class="font-label-sm text-secondary font-bold">{{ probeTemp }}°C {{ probeStatusText }}</span>
            </div>

            <!-- Mini Temperature Gauge Progress -->
            <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden relative">
              <div class="absolute left-[20%] right-[20%] top-0 bottom-0 bg-secondary-fixed"></div>
              <div class="h-full bg-secondary rounded-full" style="width: 45%;"></div>
            </div>

            <div class="flex justify-between font-label-sm text-slate-400">
              <span>0°C Min</span>
              <span class="text-secondary font-semibold">Rentang Aman 2°C - 8°C</span>
              <span>12°C Max</span>
            </div>
          </div>

          <!-- APJ Recipient Badge -->
          <div class="flex items-center gap-2 p-2.5 rounded-lg bg-surface-container-high text-on-surface-variant font-body-sm border border-surface-container-highest">
            <span class="material-symbols-outlined text-[16px] text-primary shrink-0">person_check</span>
            <span class="truncate">APJ Penerima: {{ pharmacistInCharge }}</span>
          </div>
        </div>
      </div>

      <!-- Task Actions Footer -->
      <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-surface-container/60">
        <button
          type="button"
          @click="handleProcessTask"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary-container hover:bg-primary text-on-primary font-label-lg font-bold py-3 px-8 rounded-xl shadow-md transition-all active:scale-[0.98] cursor-pointer"
        >
          <span>Buka &amp; Proses Tugas Ini</span>
          <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>

        <router-link
          to="/courier/tugas-saya"
          class="font-label-md text-slate-600 hover:text-on-surface flex items-center gap-1 transition-colors cursor-pointer font-semibold"
        >
          <span>Lihat Semua Daftar Tugas</span>
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

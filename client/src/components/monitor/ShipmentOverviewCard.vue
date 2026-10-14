<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Shipment } from '../../types/shipment'

const props = defineProps<{
  shipment: Shipment
}>()

const emit = defineEmits<{
  (e: 'toggleReadiness', isReady: boolean): void
}>()

// Interactive recipient_ready reactive state
const isReady = ref(props.shipment.recipient_ready ?? false)

watch(
  () => props.shipment.recipient_ready,
  (newVal) => {
    isReady.value = newVal ?? false
  }
)

function handleToggleReadiness() {
  isReady.value = !isReady.value
  emit('toggleReadiness', isReady.value)
}
</script>

<template>
  <section class="bg-surface-container-lowest rounded-2xl p-6 sm:p-8 shadow-xs border border-surface-container relative overflow-hidden">
    <!-- Decorative ambient corner gradient -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary-fixed/30 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Card Top: Tracking Number & PostgreSQL Status -->
    <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-surface-container">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center text-primary shadow-2xs">
          <span class="material-symbols-outlined">inventory_2</span>
        </div>
        <div>
          <div class="font-label-sm text-[11px] text-outline uppercase tracking-wider font-bold">
            Nomor Resi / AWB
          </div>
          <div class="font-headline-md text-headline-md font-bold tracking-tight text-on-surface font-mono">
            {{ shipment.tracking_number }}
          </div>
        </div>
      </div>

      <!-- PostgreSQL schema status -->
      <div class="flex items-center gap-2">
        <div
          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full font-label-md text-label-md font-bold"
          :class="shipment.status === 'pending_approval'
            ? 'bg-primary-fixed/40 text-primary border border-primary/20'
            : (shipment.status === 'in_transit' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-variant text-on-surface')"
        >
          <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
          <span>{{ shipment.status_label || 'Menunggu Persetujuan' }}</span>
          <span class="text-outline text-label-sm font-normal">({{ shipment.status }})</span>
        </div>
      </div>
    </div>

    <!-- 4-Field Clean Technical Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 py-6">
      <!-- Col 1: Sender & Destination -->
      <div class="bg-surface-container-low p-4 rounded-xl flex flex-col justify-between border border-surface-container">
        <div class="flex items-center gap-2 text-outline mb-2">
          <span class="material-symbols-outlined text-[18px]">local_shipping</span>
          <span class="font-label-sm text-[11px] uppercase tracking-wider font-bold">Rute Logistik</span>
        </div>
        <div>
          <div class="font-headline-sm text-headline-sm text-on-surface font-bold">
            {{ shipment.sender_name }}
          </div>
          <div class="font-body-sm text-[13px] text-on-surface-variant">
            {{ shipment.origin_city }}
          </div>
          <div class="flex items-center gap-1.5 my-2 text-primary font-bold">
            <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
            <span class="font-label-sm text-[11px] font-semibold">Tujuan Penerima</span>
          </div>
          <div class="font-label-lg text-label-lg text-on-surface font-bold">
            {{ shipment.recipient_name }}
          </div>
          <div class="font-body-sm text-[13px] text-on-surface-variant">
            {{ shipment.destination_city }}
          </div>
        </div>
      </div>

      <!-- Col 2: Item Summary & Count -->
      <div class="bg-surface-container-low p-4 rounded-xl flex flex-col justify-between border border-surface-container">
        <div class="flex items-center gap-2 text-outline mb-2">
          <span class="material-symbols-outlined text-[18px]">vaccines</span>
          <span class="font-label-sm text-[11px] uppercase tracking-wider font-bold">Muatan Farmasi</span>
        </div>
        <div>
          <div class="font-headline-sm text-headline-sm text-on-surface font-bold">
            {{ shipment.item_summary }}
          </div>
          <div class="font-body-md text-body-md text-on-surface-variant mt-1">
            {{ shipment.package_count }} Koli ({{ shipment.package_type || 'Insulated Shipper' }})
          </div>
          <div class="mt-4 flex items-center gap-2 flex-wrap">
            <span class="px-2.5 py-1 rounded bg-surface-container-highest text-on-surface font-label-sm text-[11px] font-bold">
              Batch: {{ shipment.batch_number || '#MCV-2026-X12' }}
            </span>
            <span class="px-2.5 py-1 rounded bg-surface-container-highest text-on-surface font-label-sm text-[11px] font-bold">
              Vol: {{ shipment.weight_kg ? shipment.weight_kg + ' kg' : '4.8 kg' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Col 3: Temperature Profile -->
      <div class="bg-surface-container-low p-4 rounded-xl flex flex-col justify-between border border-surface-container">
        <div class="flex items-center gap-2 text-outline mb-2">
          <span class="material-symbols-outlined text-[18px]">thermostat</span>
          <span class="font-label-sm text-[11px] uppercase tracking-wider font-bold">Profil Suhu CPOB</span>
        </div>
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-md text-label-md font-bold mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
            Controlled Chilled
          </div>
          <div class="font-telemetry-value text-[26px] text-on-surface font-extrabold tracking-tight">
            {{ shipment.temperature_min.toFixed(1) }}&deg;C <span class="text-body-lg font-normal text-outline">&ndash;</span> {{ shipment.temperature_max.toFixed(1) }}&deg;C
          </div>
          <div class="font-body-sm text-[12px] text-on-surface-variant mt-1">
            Ambang Kritis Toleransi: {{ shipment.tolerance_threshold || '±0.5°C (15 mnt)' }}
          </div>
        </div>
      </div>

      <!-- Col 4: Handling Instructions -->
      <div class="bg-surface-container-low p-4 rounded-xl flex flex-col justify-between border border-surface-container">
        <div class="flex items-center gap-2 text-outline mb-2">
          <span class="material-symbols-outlined text-[18px]">health_and_safety</span>
          <span class="font-label-sm text-[11px] uppercase tracking-wider font-bold">Instruksi Khusus</span>
        </div>
        <div>
          <div class="flex items-start gap-2 text-primary font-headline-sm text-headline-sm font-semibold mb-1">
            <span class="material-symbols-outlined text-[20px] shrink-0">vertical_align_top</span>
            <span class="font-bold">Jangan Dibalik</span>
          </div>
          <p class="font-body-sm text-[13px] text-on-surface-variant leading-relaxed">
            {{ shipment.handling_instructions }}
          </p>
        </div>
      </div>
    </div>

    <!-- Recipient Readiness Interactive Action Banner (recipient_ready boolean) -->
    <div
      class="mt-4 p-5 rounded-xl flex flex-col lg:flex-row items-center justify-between gap-4 transition-all duration-300 border"
      :class="isReady
        ? 'bg-secondary-container/20 border-secondary-container/40'
        : 'bg-surface-container border-surface-container-high'"
    >
      <div class="flex items-start gap-3.5">
        <div
          class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 mt-0.5 transition-colors"
          :class="isReady
            ? 'bg-secondary-container text-on-secondary-container'
            : 'bg-surface-container-highest text-primary'"
        >
          <span class="material-symbols-outlined">
            {{ isReady ? 'check_circle' : 'schedule' }}
          </span>
        </div>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-label-lg text-label-lg text-on-surface font-bold">
              Konfirmasi Kesiapan Penerima
            </span>
            <span
              class="px-2.5 py-0.5 rounded-full font-label-sm text-[11px] font-bold"
              :class="isReady
                ? 'bg-secondary-container text-on-secondary-container'
                : 'bg-primary-fixed text-primary'"
            >
              {{ isReady ? 'Fasilitas Siap (recipient_ready = true)' : 'Menunggu Konfirmasi Faskes' }}
            </span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5 leading-relaxed">
            Apakah fasilitas penerima siap menerima pengiriman sesuai jadwal (<strong class="text-on-surface font-bold">scheduled_at: {{ shipment.scheduled_at }}</strong>)?
          </p>
        </div>
      </div>

      <!-- Toggle Button -->
      <div class="flex items-center gap-3 shrink-0 w-full lg:w-auto">
        <button
          type="button"
          @click="handleToggleReadiness"
          class="w-full lg:w-auto px-5 py-2.5 rounded-lg font-label-lg text-label-lg font-bold shadow-xs active:scale-95 transition-all flex items-center justify-center gap-2 cursor-pointer"
          :class="isReady
            ? 'bg-surface-container-highest text-on-surface hover:opacity-90 border border-surface-variant'
            : 'bg-primary-container text-on-primary hover:opacity-95 shadow-md shadow-pink-200'"
        >
          <span class="material-symbols-outlined text-[18px]">check_circle</span>
          <span>{{ isReady ? 'Batalkan Kesiapan' : 'Konfirmasi Siap Menerima' }}</span>
        </button>
      </div>
    </div>
  </section>
</template>

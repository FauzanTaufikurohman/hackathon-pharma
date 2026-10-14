<script setup lang="ts">
import { ref } from 'vue'
import type { CourierTaskItem } from '../../types/shipment'

interface Props {
  task: CourierTaskItem
  hasArrived: boolean
  arrivedTime?: string
}

const props = withDefaults(defineProps<Props>(), {
  hasArrived: false,
  arrivedTime: '14:50',
})

const emit = defineEmits<{
  (e: 'confirm-arrived'): void
  (e: 'go-handover'): void
}>()

function handleArriveClick() {
  const confirmed = window.confirm(
    `Konfirmasi: Anda telah tiba di lokasi ${props.task.destination_name || 'RS Medika Sehat Jakarta'}? Status pengiriman akan diubah menjadi ARRIVED.`
  )
  if (confirmed) {
    emit('confirm-arrived')
  }
}
</script>

<template>
  <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm border border-surface-container/60">
    <!-- Header Block -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-surface-container/60">
      <div>
        <div class="flex items-center gap-2 mb-1 flex-wrap">
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-bold">
            Resi Pengiriman
          </span>
          <span class="font-headline-md text-headline-md font-bold text-on-surface tracking-tight">
            {{ task.awb }}
          </span>
          <span class="px-2.5 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm font-bold flex items-center gap-1 shadow-2xs">
            <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
            {{ hasArrived ? 'arrived (Tiba di Lokasi)' : 'in_transit (Dalam Perjalanan)' }}
          </span>
        </div>

        <p class="font-body-md text-body-md text-on-surface-variant flex items-center gap-1.5 flex-wrap">
          <span class="material-symbols-outlined text-[18px] text-primary">local_shipping</span>
          <span>Rute: {{ task.origin_name || 'Cawang Cold Hub' }}</span>
          <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
          <span>{{ task.destination_name || 'RS Medika Sehat Jakarta' }} (Instalasi Farmasi Pusat)</span>
        </p>
      </div>

      <!-- ETA Display Box -->
      <div class="flex items-center gap-3">
        <div class="bg-surface-container-low px-4 py-2 rounded-lg text-right border border-surface-container/40">
          <span class="block font-label-sm text-label-sm text-on-surface-variant font-medium">
            Estimasi Waktu Tiba
          </span>
          <span class="font-headline-sm text-headline-sm font-bold text-primary">
            {{ hasArrived ? 'Telah Tiba di Lokasi' : (task.destination_eta || '15:10 WIB (20 Menit)') }}
          </span>
        </div>
      </div>
    </div>

    <!-- 3-Step Action Sequence -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
      <!-- Step 1: Completed -->
      <div class="bg-secondary-fixed/30 rounded-xl p-4 flex items-start gap-3 border border-secondary/20">
        <div class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0 shadow-2xs">
          <span class="material-symbols-outlined text-[18px]">check</span>
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-1.5">
            <span class="font-label-md text-label-md font-bold text-on-secondary-fixed-variant">
              1. Paket Berangkat
            </span>
            <span class="font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-mono font-bold">
              depart
            </span>
          </div>
          <p class="font-body-sm text-body-sm text-on-secondary-fixed-variant mt-0.5">
            Diserahkan &amp; dicek Hub Cawang
          </p>
          <span class="font-label-sm text-label-sm font-semibold text-secondary mt-1 block">
            13:45 WIB • Terverifikasi
          </span>
        </div>
      </div>

      <!-- Step 2: Active Primary CTA or Completed -->
      <button
        v-if="!hasArrived"
        type="button"
        id="arrive-cta"
        @click="handleArriveClick"
        class="relative group bg-primary text-on-primary rounded-xl p-4 text-left shadow-md hover:bg-on-primary-fixed-variant transition-all cursor-pointer active:scale-[0.98]"
      >
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-full bg-on-primary text-primary flex items-center justify-center shrink-0 font-bold font-headline-sm text-headline-sm relative">
            <span class="w-3 h-3 rounded-full bg-primary animate-ping absolute"></span>
            2
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <span class="font-label-md text-label-md font-bold text-on-primary">
                2. Tiba di RS Penerima
              </span>
              <span class="font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-on-primary text-primary font-bold font-mono">
                arrive
              </span>
            </div>
            <p class="font-body-sm text-body-sm text-on-primary-container mt-0.5">
              Tekan saat kendaraan parkir di RS Medika
            </p>
            <div class="mt-2 flex items-center gap-1 font-label-md text-label-md font-bold bg-white/20 px-2.5 py-1 rounded-lg w-fit shadow-2xs group-hover:bg-white/30 transition-colors">
              <span class="material-symbols-outlined text-[16px]">touch_app</span>
              <span>Konfirmasi Tiba (Status → Arrived)</span>
            </div>
          </div>
        </div>
      </button>

      <!-- Step 2 Completed State -->
      <div
        v-else
        class="bg-secondary-fixed/40 rounded-xl p-4 flex items-start gap-3 border border-secondary/30 animate-in fade-in zoom-in-95 duration-200"
      >
        <div class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0 shadow-2xs">
          <span class="material-symbols-outlined text-[18px]">check</span>
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-1.5">
            <span class="font-label-md text-label-md font-bold text-on-secondary-fixed-variant">
              2. Tiba di Lokasi
            </span>
            <span class="font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-bold font-mono">
              arrived
            </span>
          </div>
          <p class="font-body-sm text-body-sm text-on-secondary-fixed-variant mt-0.5">
            Tiba pada {{ arrivedTime }} WIB
          </p>
          <span class="font-label-sm text-label-sm font-semibold text-secondary mt-1 block">
            Siap Lanjut ke Tab Serah Terima
          </span>
        </div>
      </div>

      <!-- Step 3: Pending or Ready -->
      <div
        class="rounded-xl p-4 flex items-start gap-3 transition-all"
        :class="hasArrived
          ? 'bg-primary-fixed/20 border border-primary/30 cursor-pointer hover:bg-primary-fixed/30'
          : 'bg-surface-container-low opacity-60 border border-surface-container/40'"
        @click="hasArrived && emit('go-handover')"
      >
        <div
          class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 font-bold font-label-md text-label-md shadow-2xs"
          :class="hasArrived ? 'bg-primary text-white' : 'bg-surface-container-high text-on-surface-variant'"
        >
          3
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-1.5">
            <span
              class="font-label-md text-label-md font-bold"
              :class="hasArrived ? 'text-primary' : 'text-on-surface-variant'"
            >
              3. Serah Terima Fisik
            </span>
            <span class="font-label-sm text-label-sm px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-mono">
              handover
            </span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            Scan barcode obat &amp; tanda tangan digital APJ
          </p>
          <span
            class="font-label-sm text-label-sm font-semibold mt-1 block"
            :class="hasArrived ? 'text-primary' : 'text-on-surface-variant'"
          >
            {{ hasArrived ? 'Klik untuk Lanjut ke Serah Terima →' : 'Menunggu status Tiba' }}
          </span>
        </div>
      </div>
    </div>
  </section>
</template>

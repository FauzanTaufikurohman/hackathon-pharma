<script setup lang="ts">
import { computed } from 'vue'
import type { AdminApprovalShipment } from '../../types/shipment'

const props = defineProps<{
  shipments: AdminApprovalShipment[]
  selectedAwb: string
}>()

const emit = defineEmits<{
  (e: 'select-shipment', awb: string): void
}>()

const activeShipment = computed<AdminApprovalShipment>(() => {
  return props.shipments.find((s) => s.awb === props.selectedAwb) || props.shipments[0]!
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4 mb-6 border border-surface-container-low">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <!-- Active Shipment Info -->
      <div class="flex items-start sm:items-center gap-3 flex-wrap">
        <div class="bg-surface-container p-2.5 rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-primary text-2xl">vaccines</span>
        </div>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-headline-sm text-headline-sm text-on-surface font-bold">AWB: {{ activeShipment.awb }}</span>
            <span
              v-if="activeShipment.status === 'pending_approval'"
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-label-sm text-label-sm font-semibold"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
              pending_approval (Menunggu Persetujuan Admin)
            </span>
            <span
              v-else-if="activeShipment.status === 'approved'"
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
              approved (Disetujui Admin)
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-error-container text-error font-label-sm text-label-sm font-semibold"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
              held (Kargo Ditahan)
            </span>
            <span class="bg-surface-container-high text-on-surface font-label-sm text-label-sm px-2 py-0.5 rounded-md font-semibold">
              Batch {{ activeShipment.batch_number }}
            </span>
          </div>
          <div class="flex items-center gap-2 font-body-sm text-body-sm text-on-surface-variant mt-0.5 flex-wrap">
            <span class="font-semibold text-on-surface">{{ activeShipment.commodity }} ({{ activeShipment.koli }})</span>
            <span>•</span>
            <span class="flex items-center gap-1">
              <span class="material-symbols-outlined text-sm">warehouse</span> {{ activeShipment.origin_hub }}
              <span class="material-symbols-outlined text-sm">arrow_forward</span>
              <span class="material-symbols-outlined text-sm">local_hospital</span> {{ activeShipment.destination }}
            </span>
          </div>
        </div>
      </div>

      <!-- Shipment Queue Chips -->
      <div class="flex items-center gap-2 pt-2 lg:pt-0 shrink-0">
        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold">Pilih Manifest:</span>
        <div class="flex items-center gap-1.5 bg-surface-container-low p-1 rounded-lg">
          <button
            v-for="s in shipments"
            :key="s.awb"
            type="button"
            @click="emit('select-shipment', s.awb)"
            :class="[
              'px-3 py-1.5 rounded-md font-label-md text-label-md flex items-center gap-1.5 transition-all cursor-pointer',
              s.awb === selectedAwb
                ? 'bg-surface-container-lowest shadow-sm text-primary font-bold'
                : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-lowest/60',
            ]"
          >
            <span
              :class="[
                'w-2 h-2 rounded-full',
                s.awb === selectedAwb ? 'bg-primary' : 'bg-on-surface-variant/40',
              ]"
            ></span>
            {{ s.awb }} {{ s.awb === selectedAwb ? '(Aktif)' : (s.awb === 'PHA-260109' ? '(Vaksin Rabies - Antrean #2)' : '') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

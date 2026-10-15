<script setup lang="ts">
import type { CheckerHistoryItem } from '../../types/shipment'

interface Props {
  isOpen: boolean
  item: CheckerHistoryItem | null
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div
    v-if="isOpen && item"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in"
  >
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-surface-container">
      <!-- Modal Header -->
      <div class="p-5 border-b border-surface-container flex items-center justify-between bg-surface-container-low">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg bg-primary text-white flex items-center justify-center shadow-xs">
            <span class="material-symbols-outlined text-xl">fact_check</span>
          </div>
          <div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
              Detail Formulir Pemeriksaan Awal (Initial Exam)
            </h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant font-mono">
              AWB: {{ item.awb }} • Waktu Submit: {{ item.submitted_at }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
        <!-- Commodity info -->
        <div class="p-4 bg-surface-container-low rounded-xl border border-surface-container/60">
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold tracking-wider block mb-1">
            Komoditas Farmasi
          </span>
          <div class="flex items-center justify-between">
            <h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">
              {{ item.commodity_title }}
            </h4>
            <span class="px-2.5 py-0.5 rounded-full bg-secondary-container/50 text-secondary font-label-sm text-label-sm font-bold">
              {{ item.package_count_str }}
            </span>
          </div>
        </div>

        <!-- Checklist grid -->
        <div>
          <span class="font-label-sm text-label-sm text-on-surface-variant uppercase font-bold tracking-wider block mb-2">
            Verifikasi Fisik &amp; Integritas Kemasan (CDOB BPOM)
          </span>
          <div class="grid grid-cols-2 gap-3">
            <div
              v-for="(p, idx) in item.checklist_params"
              :key="idx"
              class="p-3 bg-surface-container-low rounded-xl border border-surface-container/40"
            >
              <span class="font-body-sm text-body-sm text-on-surface-variant block">{{ p.label }}</span>
              <div class="flex items-center gap-1.5 font-label-md text-label-md text-secondary font-bold mt-1">
                <span class="material-symbols-outlined text-base">{{ p.icon }}</span>
                <span>{{ p.value }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Telemetry Details -->
        <div class="p-4 bg-surface-container-high/30 rounded-xl border border-surface-container flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-secondary text-2xl">ac_unit</span>
            <div>
              <span class="font-body-sm text-body-sm text-on-surface-variant">Sensor Telemetri Dingin Internal:</span>
              <div class="font-headline-sm text-headline-sm text-on-surface font-bold">
                {{ item.cold_chain_strip.value }}
              </div>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full bg-secondary-container text-secondary font-label-sm text-label-sm font-bold shadow-2xs">
            {{ item.cold_chain_strip.badge_text }}
          </span>
        </div>

        <!-- Admin Note -->
        <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
          <span class="font-label-sm text-label-sm text-tertiary uppercase font-bold tracking-wider block mb-1">
            Status Gatekeeper QA Control Tower
          </span>
          <p class="font-body-sm text-body-sm text-on-surface leading-relaxed">
            {{ item.admin_decision.note }}
          </p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-4 bg-surface-container-low border-t border-surface-container flex items-center justify-between">
        <span class="font-body-sm text-body-sm text-on-surface-variant">
          Pemeriksa QC: <strong>Hendra Wijaya (ID: CHK-012)</strong>
        </span>
        <button
          type="button"
          @click="emit('close')"
          class="px-5 py-2.5 rounded-xl bg-primary text-white font-label-md text-label-md font-bold transition-all cursor-pointer shadow-xs hover:bg-primary-container"
        >
          Tutup Formulir
        </button>
      </div>
    </div>
  </div>
</template>

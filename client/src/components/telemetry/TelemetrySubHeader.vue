<script setup lang="ts">
import type { Shipment } from '../../types/shipment'

const props = defineProps<{
  shipment: Shipment
}>()

const emit = defineEmits<{
  (e: 'copyAwb'): void
}>()

function copyToClipboard() {
  navigator.clipboard.writeText(props.shipment.tracking_number)
  emit('copyAwb')
}
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
    <div class="flex flex-col gap-space-xs">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-space-xs text-on-surface-variant font-label-sm text-label-sm tracking-wide">
        <router-link to="/paket-saya" class="hover:text-primary transition-colors cursor-pointer uppercase font-semibold">
          Portal Buyer / Faskes
        </router-link>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-primary font-bold uppercase">Lacak Kiriman &amp; Suhu</span>
      </div>

      <!-- Shipment Title & Status Pill -->
      <div class="flex items-center gap-space-md flex-wrap mt-1">
        <h1 class="font-headline-lg text-[24px] sm:text-[28px] font-extrabold text-on-surface tracking-tight">
          {{ shipment.item_summary }} ({{ shipment.package_count }} Koli)
        </h1>
        <div class="flex items-center gap-space-xs bg-primary-fixed/60 text-primary px-3 py-1 rounded-full font-label-md text-label-md font-bold">
          <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
          <span class="w-1.5 h-1.5 -ml-2.5 rounded-full bg-primary"></span>
          <span>Dalam perjalanan (in_transit)</span>
        </div>
      </div>
    </div>

    <!-- AWB Copy Box -->
    <div class="flex items-center gap-space-sm bg-surface-container-lowest p-2 rounded-xl shadow-xs border border-surface-container self-start md:self-auto shrink-0">
      <div class="px-3 py-1">
        <div class="font-label-sm text-[10px] text-on-surface-variant uppercase font-bold tracking-wider">No. Resi AWB</div>
        <div class="font-headline-sm text-headline-sm font-extrabold text-on-surface tracking-wider font-mono">
          {{ shipment.tracking_number }}
        </div>
      </div>
      <button
        type="button"
        @click="copyToClipboard"
        class="w-8 h-8 rounded-lg bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary-fixed flex items-center justify-center transition-colors cursor-pointer active:scale-95"
        title="Salin No Resi"
      >
        <span class="material-symbols-outlined text-[18px]">content_copy</span>
      </button>
    </div>
  </div>
</template>

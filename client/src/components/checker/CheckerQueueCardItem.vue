<script setup lang="ts">
import type { CheckerQueueItem } from '../../types/shipment'

interface Props {
  item: CheckerQueueItem
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'action-click', item: CheckerQueueItem): void
  (e: 'copy-awb', awb: string): void
}>()
</script>

<template>
  <div
    class="bg-surface-container-lowest rounded-2xl p-6 shadow-md transition-all hover:shadow-lg flex flex-col xl:flex-row gap-6 relative overflow-hidden border border-surface-container/60"
  >
    <!-- Left Accent Stripe -->
    <div
      class="absolute left-0 top-0 bottom-0 w-1.5"
      :class="{
        'bg-primary-container': item.accent_color === 'primary',
        'bg-tertiary-container': item.accent_color === 'tertiary',
        'bg-secondary': item.accent_color === 'secondary',
      }"
    ></div>

    <!-- Left: Tracking & Commodity Information -->
    <div class="xl:w-4/12 flex flex-col justify-between pl-2 xl:pl-3">
      <div>
        <div class="flex items-center gap-2.5 mb-2.5 flex-wrap">
          <span class="bg-surface-container-high text-on-surface font-label-md text-label-md px-2.5 py-1 rounded tracking-wide font-mono font-bold shadow-2xs">
            {{ item.awb }}
          </span>

          <button
            type="button"
            @click="emit('copy-awb', item.awb)"
            class="text-on-surface-variant hover:text-primary p-1 rounded transition-colors cursor-pointer"
            title="Salin No. Resi"
          >
            <span class="material-symbols-outlined text-base">content_copy</span>
          </button>

          <!-- Temperature / Recheck Tag -->
          <span
            v-if="item.status === 'recheck'"
            class="bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm px-2.5 py-0.5 rounded-full flex items-center gap-1 font-semibold shadow-2xs"
          >
            <span class="material-symbols-outlined text-xs">warning</span>
            <span>{{ item.temp_range_label }}</span>
          </span>
          <span
            v-else
            class="bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm px-2.5 py-0.5 rounded-full flex items-center gap-1 font-semibold shadow-2xs"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
            <span>{{ item.temp_range_label }}</span>
          </span>
        </div>

        <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-1">
          {{ item.commodity_title }}
        </h3>
        <p class="font-body-md text-body-md text-on-surface-variant">
          {{ item.package_desc }}
        </p>
      </div>

      <div class="mt-4 pt-3 flex items-center gap-2 bg-surface-container-low px-3 py-2 rounded-lg border border-surface-container/40">
        <span
          class="material-symbols-outlined text-base"
          :class="item.status === 'recheck' ? 'text-tertiary' : 'text-secondary'"
        >
          verified
        </span>
        <span class="font-label-sm text-label-sm text-on-surface-variant">Batch ID:</span>
        <span class="font-label-md text-label-md text-on-surface font-mono font-bold">
          {{ item.batch_id }}
        </span>
      </div>
    </div>

    <!-- Middle: Route & CPOB Handling Directives -->
    <div class="xl:w-5/12 flex flex-col justify-between bg-surface-container-low p-4 rounded-xl border border-surface-container/40">
      <div>
        <div class="flex items-start gap-2.5 mb-3">
          <span
            class="material-symbols-outlined text-lg mt-0.5"
            :class="{
              'text-primary': item.accent_color === 'primary',
              'text-tertiary': item.accent_color === 'tertiary',
              'text-secondary': item.accent_color === 'secondary',
            }"
          >
            alt_route
          </span>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 font-label-sm text-label-sm text-on-surface-variant">
              <span class="shrink-0 font-medium">Asal:</span>
              <span class="text-on-surface font-semibold truncate">{{ item.origin_label }}</span>
            </div>
            <div class="flex items-center gap-1.5 font-label-sm text-label-sm text-on-surface-variant mt-0.5">
              <span class="shrink-0 font-medium">Tujuan:</span>
              <span class="text-on-surface font-semibold truncate">{{ item.destination_label }}</span>
            </div>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-3 rounded-lg shadow-sm border border-surface-container/40">
          <span
            class="font-label-sm text-label-sm uppercase font-bold tracking-wider block mb-1"
            :class="{
              'text-primary': item.accent_color === 'primary',
              'text-tertiary': item.accent_color === 'tertiary',
              'text-secondary': item.accent_color === 'secondary',
            }"
          >
            {{ item.instruction_title }}
          </span>
          <p class="font-body-sm text-body-sm text-on-surface leading-snug">
            {{ item.instruction_desc }}
          </p>
        </div>
      </div>

      <div class="mt-3 flex items-center justify-between font-label-md text-label-md pt-1">
        <span class="text-on-surface-variant flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">
            {{ item.status === 'recheck' ? 'notification_important' : 'schedule' }}
          </span>
          {{ item.sla_status_str }}
        </span>
        <span
          class="font-bold"
          :class="{
            'text-primary': item.accent_color === 'primary',
            'text-tertiary': item.accent_color === 'tertiary',
            'text-on-surface': item.accent_color === 'secondary',
          }"
        >
          {{ item.sla_target_str }}
        </span>
      </div>
    </div>

    <!-- Right: Status Badge & Primary Action -->
    <div class="xl:w-3/12 flex flex-col justify-between items-start xl:items-end">
      <!-- Status Badge -->
      <div class="flex items-center gap-2 mb-4 xl:mb-0">
        <span v-if="item.status === 'inspection'" class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
        </span>

        <span
          class="font-label-md text-label-md px-3 py-1 rounded-full font-bold shadow-2xs"
          :class="{
            'bg-primary-fixed text-primary': item.status === 'inspection',
            'bg-tertiary-fixed text-on-tertiary-fixed': item.status === 'recheck',
            'bg-surface-container-high text-on-surface-variant': item.status === 'scheduled',
          }"
        >
          {{ item.status_label }}
        </span>
      </div>

      <!-- Action Button -->
      <div class="w-full flex flex-col gap-2 mt-auto">
        <button
          type="button"
          @click="emit('action-click', item)"
          class="w-full font-label-lg text-label-lg px-5 py-3 rounded-xl shadow-md flex items-center justify-center gap-2 transition-all cursor-pointer font-bold active:scale-95"
          :class="{
            'bg-primary-container hover:bg-primary text-on-primary': item.status === 'inspection',
            'bg-surface-container-lowest text-primary hover:bg-primary-fixed/30 border border-surface-container': item.status === 'recheck',
            'bg-surface-container-low hover:bg-surface-container-high text-on-surface border border-surface-container/60': item.status === 'scheduled',
          }"
        >
          <span>{{ item.cta_label }}</span>
          <span class="material-symbols-outlined text-lg">
            {{ item.cta_icon }}
          </span>
        </button>

        <span class="font-body-sm text-body-sm text-on-surface-variant text-center">
          {{ item.inspector_note }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { CourierTaskItem } from '../../types/shipment'

interface Props {
  task: CourierTaskItem
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'action-click', task: CourierTaskItem): void
}>()
</script>

<template>
  <div
    class="task-card bg-surface-container-lowest rounded-2xl p-6 shadow-sm transition-all relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-6 hover:shadow-md border border-surface-container/60"
    :data-status="task.status"
  >
    <!-- Left Status Stripe -->
    <div
      class="absolute left-0 top-0 bottom-0 w-1.5"
      :class="{
        'bg-primary-container': task.status === 'assigned',
        'bg-tertiary': task.status === 'in_transit',
        'bg-secondary': task.status === 'delivered',
        'bg-outline-variant': task.status === 'hold'
      }"
    ></div>

    <!-- Left Column: Cargo Identity & Cold-chain IoT -->
    <div class="flex-1 min-w-0 pl-2 lg:pl-3 flex flex-col gap-2.5">
      <div class="flex flex-wrap items-center gap-2.5">
        <span class="font-headline-sm font-bold tracking-tight text-on-surface">
          AWB: {{ task.awb }}
        </span>

        <!-- Temperature Profile Tag -->
        <span
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-label-sm font-semibold shadow-2xs"
          :class="task.temperature_profile_label.includes('Ambient')
            ? 'bg-surface-container-high text-on-surface'
            : 'bg-secondary-container text-on-secondary-container'"
        >
          <span class="material-symbols-outlined text-[14px]">
            {{ task.temperature_profile_label.includes('Ambient') ? 'thermostat_auto' : 'ac_unit' }}
          </span>
          {{ task.temperature_profile_label }}
        </span>

        <!-- Urgent/Status Badge -->
        <span
          v-if="task.status === 'assigned'"
          class="px-2 py-0.5 rounded bg-primary-fixed text-primary-container font-label-sm font-bold uppercase shadow-2xs"
        >
          {{ task.urgent_badge }}
        </span>
        <span
          v-else-if="task.status === 'in_transit'"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-tertiary-fixed text-on-tertiary-fixed-variant font-label-sm font-bold shadow-2xs"
        >
          <span class="relative flex h-1.5 w-1.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-tertiary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-tertiary"></span>
          </span>
          {{ task.urgent_badge }}
        </span>
        <span
          v-else-if="task.status === 'delivered'"
          class="px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-label-sm font-bold shadow-2xs"
        >
          {{ task.urgent_badge }}
        </span>
      </div>

      <div class="flex flex-col gap-1">
        <p class="font-headline-sm text-on-surface font-semibold flex items-center gap-2">
          <span>{{ task.item_title }}</span>
          <span class="font-body-sm text-on-surface-variant font-normal">{{ task.batch_number }}</span>
        </p>

        <!-- IoT probe and seal details -->
        <div class="flex flex-wrap items-center gap-3 text-on-surface-variant font-body-sm">
          <span class="flex items-center gap-1 text-secondary font-medium">
            <span class="material-symbols-outlined text-[16px]">
              {{ task.status === 'delivered' ? 'check_circle' : (task.status === 'in_transit' ? 'device_thermostat' : 'sensors') }}
            </span>
            <span>{{ task.probe_temp_str }}</span>
          </span>

          <template v-if="task.battery_pct">
            <span class="text-outline">•</span>
            <span class="flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px] text-secondary">battery_horiz_075</span>
              <span>IoT Bat: {{ task.battery_pct }}%</span>
            </span>
          </template>

          <span class="text-outline">•</span>
          <span class="flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">
              {{ task.status === 'delivered' ? 'assignment_turned_in' : (task.status === 'in_transit' ? 'lock_clock' : 'lock') }}
            </span>
            <span v-if="task.status === 'assigned'">
              Segel Fisik: <strong class="font-mono text-on-surface">{{ task.seal_str }}</strong>
            </span>
            <span v-else>{{ task.seal_str }}</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Middle Column: Route & Target Schedule -->
    <div class="flex-1 min-w-0 flex flex-col gap-2.5 bg-surface-container-low/60 p-4 rounded-xl border border-surface-container/40">
      <div class="flex items-start gap-3">
        <!-- Visual Route Nodes -->
        <div class="flex flex-col items-center pt-0.5 shrink-0">
          <span
            class="w-2.5 h-2.5 rounded-full"
            :class="task.status === 'delivered' || task.status === 'in_transit' ? 'bg-secondary' : 'bg-outline'"
          ></span>
          <span
            class="w-0.5 h-7"
            :class="{
              'bg-surface-variant': task.status === 'assigned',
              'bg-tertiary': task.status === 'in_transit',
              'bg-secondary': task.status === 'delivered'
            }"
          ></span>
          <span
            class="w-2.5 h-2.5 rounded-full"
            :class="{
              'bg-primary-container': task.status === 'assigned',
              'bg-tertiary animate-pulse': task.status === 'in_transit',
              'bg-secondary': task.status === 'delivered'
            }"
          ></span>
        </div>

        <div class="flex flex-col gap-1.5 flex-1 min-w-0">
          <div class="flex items-center justify-between text-on-surface-variant font-body-sm">
            <span class="truncate">Asal: <strong class="text-on-surface font-semibold">{{ task.origin_name }}</strong></span>
            <span class="text-[11px] font-mono text-outline shrink-0">{{ task.origin_time }}</span>
          </div>
          <div class="flex items-center justify-between font-body-sm">
            <span class="truncate text-on-surface font-bold">Tujuan: {{ task.destination_name }}</span>
            <span
              class="text-[11px] font-semibold shrink-0"
              :class="{
                'text-primary-container': task.status === 'assigned',
                'text-tertiary': task.status === 'in_transit',
                'text-secondary': task.status === 'delivered'
              }"
            >
              {{ task.destination_eta }}
            </span>
          </div>
          <p class="text-on-surface-variant text-[11px] line-clamp-1">
            {{ task.destination_address }}
          </p>
        </div>
      </div>

      <!-- Route footnote / hardware container -->
      <div class="flex items-center justify-between pt-1 text-on-surface-variant font-body-sm border-t border-surface-container/60">
        <template v-if="task.status === 'in_transit'">
          <span class="flex items-center gap-1 text-[11px] font-medium text-on-surface">
            <span class="material-symbols-outlined text-[14px] text-primary-container">near_me</span>
            {{ task.route_progress_note }}
          </span>
          <span class="text-[11px] text-on-surface-variant truncate">
            Penerima: {{ task.recipient_person }}
          </span>
        </template>
        <template v-else-if="task.status === 'delivered'">
          <span class="flex items-center gap-1 text-[11px] truncate">
            <span class="material-symbols-outlined text-[14px] text-secondary">verified_user</span>
            {{ task.recipient_person }}
          </span>
          <span class="text-[11px] font-mono text-secondary font-bold">
            {{ task.pod_number }}
          </span>
        </template>
        <template v-else>
          <span class="flex items-center gap-1 text-[11px]">
            <span class="material-symbols-outlined text-[14px] text-tertiary">inventory_2</span>
            {{ task.cooler_box_desc }}
          </span>
          <span class="text-[11px] font-medium text-primary-container">
            {{ task.tolerance_label }}
          </span>
        </template>
      </div>
    </div>

    <!-- Right Column: Status & Trigger CTA -->
    <div class="flex lg:flex-col items-end justify-between lg:justify-center gap-3 shrink-0 pt-2 lg:pt-0">
      <!-- Status Badge -->
      <span
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-label-sm font-semibold shadow-2xs"
        :class="{
          'bg-primary-fixed text-primary-container': task.status === 'assigned',
          'bg-tertiary-fixed text-on-tertiary-fixed-variant': task.status === 'in_transit',
          'bg-secondary-container text-on-secondary-container': task.status === 'delivered'
        }"
      >
        <span class="material-symbols-outlined text-[14px]">
          {{ task.status === 'assigned' ? 'notifications_active' : (task.status === 'in_transit' ? 'directions_bike' : 'done_all') }}
        </span>
        {{ task.status_label }}
      </span>

      <!-- Action Button -->
      <button
        type="button"
        @click="emit('action-click', task)"
        class="font-label-md px-5 py-2.5 rounded-xl shadow-xs transition-all flex items-center gap-2 group cursor-pointer font-bold active:scale-[0.98]"
        :class="{
          'bg-primary-container hover:bg-primary text-on-primary': task.status === 'assigned',
          'bg-surface-container-lowest text-primary-container hover:bg-surface-container-low border border-surface-container': task.status === 'in_transit',
          'bg-surface-container hover:bg-surface-container-high text-on-surface border border-surface-container-high': task.status === 'delivered'
        }"
      >
        <span>{{ task.cta_label }}</span>
        <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">
          {{ task.cta_type === 'view_pod' ? 'description' : 'arrow_forward' }}
        </span>
      </button>

      <span class="font-body-sm text-[11px] text-on-surface-variant hidden lg:inline-block">
        {{ task.hint_text }}
      </span>
    </div>
  </div>
</template>

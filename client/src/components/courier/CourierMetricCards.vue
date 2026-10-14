<script setup lang="ts">
import type { CourierMetrics } from '../../types/shipment'
import { mockCourierMetrics } from '../../data/mockShipments'

interface Props {
  metrics?: CourierMetrics
}

const props = withDefaults(defineProps<Props>(), {
  metrics: () => mockCourierMetrics,
})

const emit = defineEmits<{
  (e: 'card-click', type: string): void
}>()
</script>

<template>
  <!-- 4 Summary Metric Cards -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
    <!-- Card 1: Tugas Baru Masuk -->
    <div
      @click="emit('card-click', 'new')"
      class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm hover:shadow-md transition-all relative overflow-hidden group cursor-pointer border border-surface-container/60 hover:-translate-y-0.5"
    >
      <div class="absolute top-0 left-0 h-1 w-full bg-primary-container"></div>
      <div class="flex items-center justify-between gap-2">
        <span class="font-label-md text-on-surface-variant">Tugas Baru Masuk</span>
        <span class="font-label-sm px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant uppercase font-bold">
          Respon Cepat
        </span>
      </div>
      <div class="mt-3 flex items-baseline justify-between">
        <div class="font-telemetry-value text-primary-container">
          {{ metrics.new_tasks_count }} <span class="font-headline-sm text-on-surface-variant font-normal">Tugas</span>
        </div>
        <span class="material-symbols-outlined text-[28px] text-primary-container/20 group-hover:text-primary-container/40 transition-colors">
          notification_important
        </span>
      </div>
      <p class="font-body-sm text-on-surface-variant mt-2 flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px] text-primary-container">pending_actions</span>
        <span>Butuh konfirmasi penugasan</span>
      </p>
    </div>

    <!-- Card 2: Sedang Diantar -->
    <div
      @click="emit('card-click', 'in_transit')"
      class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm hover:shadow-md transition-all relative overflow-hidden group cursor-pointer border border-surface-container/60 hover:-translate-y-0.5"
    >
      <div class="absolute top-0 left-0 h-1 w-full bg-tertiary"></div>
      <div class="flex items-center justify-between gap-2">
        <span class="font-label-md text-on-surface-variant">Sedang Diantar</span>
        <span class="font-label-sm px-2.5 py-0.5 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-bold">
          On-Route
        </span>
      </div>
      <div class="mt-3 flex items-baseline justify-between">
        <div class="font-telemetry-value text-tertiary">
          {{ metrics.on_route_count }} <span class="font-headline-sm text-on-surface-variant font-normal">Tugas</span>
        </div>
        <span class="material-symbols-outlined text-[28px] text-tertiary/20 group-hover:text-tertiary/40 transition-colors">
          local_shipping
        </span>
      </div>
      <p class="font-body-sm text-on-surface-variant mt-2 flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px] text-tertiary">navigation</span>
        <span>Dalam perjalanan rute Cawang</span>
      </p>
    </div>

    <!-- Card 3: Ditahan / Kendala -->
    <div
      @click="emit('card-click', 'hold')"
      class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm hover:shadow-md transition-all relative overflow-hidden group cursor-pointer border border-surface-container/60 hover:-translate-y-0.5"
    >
      <div class="absolute top-0 left-0 h-1 w-full bg-outline-variant"></div>
      <div class="flex items-center justify-between gap-2">
        <span class="font-label-md text-on-surface-variant">Ditahan / Kendala</span>
        <span class="font-label-sm px-2.5 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant font-bold">
          Aman
        </span>
      </div>
      <div class="mt-3 flex items-baseline justify-between">
        <div class="font-telemetry-value text-on-surface">
          {{ metrics.issues_hold_count }} <span class="font-headline-sm text-on-surface-variant font-normal">Tugas</span>
        </div>
        <span class="material-symbols-outlined text-[28px] text-on-surface/20 group-hover:text-on-surface/30 transition-colors">
          verified_user
        </span>
      </div>
      <p class="font-body-sm text-on-surface-variant mt-2 flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px] text-secondary">check_circle</span>
        <span>Insiden operasional nihil</span>
      </p>
    </div>

    <!-- Card 4: Selesai Hari Ini -->
    <div
      @click="emit('card-click', 'completed')"
      class="bg-surface-container-lowest rounded-2xl p-5 shadow-sm hover:shadow-md transition-all relative overflow-hidden group cursor-pointer border border-surface-container/60 hover:-translate-y-0.5"
    >
      <div class="absolute top-0 left-0 h-1 w-full bg-secondary"></div>
      <div class="flex items-center justify-between gap-2">
        <span class="font-label-md text-on-surface-variant">Selesai Hari Ini</span>
        <span class="font-label-sm px-2.5 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-bold">
          100% CDOB
        </span>
      </div>
      <div class="mt-3 flex items-baseline justify-between">
        <div class="font-telemetry-value text-secondary">
          {{ metrics.completed_today_count }} <span class="font-headline-sm text-on-surface-variant font-normal">Tugas</span>
        </div>
        <span class="material-symbols-outlined text-[28px] text-secondary/20 group-hover:text-secondary/40 transition-colors">
          task_alt
        </span>
      </div>
      <p class="font-body-sm text-on-surface-variant mt-2 flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px] text-secondary">verified</span>
        <span>Serah terima e-POD tuntas</span>
      </p>
    </div>
  </section>
</template>

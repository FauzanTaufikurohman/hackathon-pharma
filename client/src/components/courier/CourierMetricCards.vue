<script setup lang="ts">
import type { CourierMetrics } from '../../types/shipment'
defineProps<{ metrics: CourierMetrics }>()
defineEmits<{ 'card-click': [type: string] }>()
const cards = [
  {
    key: 'new_tasks_count',
    type: 'new',
    label: 'Perlu jawaban',
    note: 'Penugasan baru',
  },
  {
    key: 'on_route_count',
    type: 'in_transit',
    label: 'Sedang diantar',
    note: 'Pengantaran aktif',
  },
  {
    key: 'issues_hold_count',
    type: 'hold',
    label: 'Ditahan / kendala',
    note: 'Tunggu keputusan Admin',
  },
  {
    key: 'completed_today_count',
    type: 'completed',
    label: 'Selesai hari ini',
    note: 'Kiriman ditutup hari ini (WIB)',
  },
] as const
</script>
<template>
  <section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <button
      v-for="card in cards"
      :key="card.key"
      type="button"
      class="live-panel text-left focus-visible:outline-2 focus-visible:outline-primary"
      @click="$emit('card-click', card.type)"
    >
      <p>{{ card.label }}</p>
      <p
        class="text-3xl tabular-nums font-semibold mt-3"
        :class="
          card.type === 'hold' && metrics.issues_hold_count
            ? 'text-error'
            : 'text-primary'
        "
      >
        {{ metrics[card.key] }}
      </p>
      <p class="mt-3 text-on-surface-variant">{{ card.note }}</p>
    </button>
  </section>
</template>

<script setup lang="ts">
import type { LiveShipment } from '../../services/models'
import { dateText } from '../../services/models'
defineProps<{ shipment: LiveShipment }>()
</script>
<template>
  <section class="live-panel">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="min-w-0">
        <p class="text-primary font-semibold break-all">
          {{ shipment.tracking_number }}
        </p>
        <h2 class="text-xl font-semibold mt-1">{{ shipment.item_summary }}</h2>
      </div>
      <div
        class="live-badge"
        :class="
          ['held', 'deviation_hold', 'rejected'].includes(shipment.status)
            ? 'bg-error-container text-on-error-container'
            : 'bg-secondary-container text-on-secondary-container'
        "
      >
        {{ shipment.status_label }}
      </div>
    </div>
    <dl class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-6">
      <div>
        <dt>Pengirim</dt>
        <dd>{{ shipment.sender_name }} · {{ shipment.origin_city }}</dd>
      </div>
      <div>
        <dt>Tujuan</dt>
        <dd>{{ shipment.recipient_name }} · {{ shipment.destination_city }}</dd>
      </div>
      <div>
        <dt>Jadwal</dt>
        <dd>{{ dateText(shipment.scheduled_at) }}</dd>
      </div>
      <div>
        <dt>Muatan</dt>
        <dd>{{ shipment.package_count }} koli · {{ shipment.weight_kg }} kg</dd>
      </div>
      <div>
        <dt>Rentang suhu</dt>
        <dd>
          {{ shipment.temperature_min }}–{{ shipment.temperature_max }} °C
        </dd>
      </div>
      <div>
        <dt>Suhu terakhir</dt>
        <dd>
          {{
            shipment.current_temperature === null
              ? 'Belum ada sampel'
              : shipment.current_temperature + ' °C'
          }}
          · {{ shipment.temp_label }}
        </dd>
      </div>
    </dl>
    <p class="mt-5">{{ shipment.address }}</p>
    <p class="mt-2 text-on-surface-variant">
      {{ shipment.handling_instructions }}
    </p>
    <p v-if="shipment.is_demo" class="mt-4 text-tertiary">
      Data contoh. Sampel simulasi tidak membuktikan kondisi obat atau lokasi
      kurir nyata.
    </p>
  </section>
</template>

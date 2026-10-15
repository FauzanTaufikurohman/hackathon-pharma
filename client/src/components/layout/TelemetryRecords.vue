<script setup lang="ts">
import { computed } from 'vue'
import type { Sample } from '../../services/models'
import { dateText } from '../../services/models'
const props = defineProps<{
  samples: Sample[]
  minimum: number
  maximum: number
}>()
const latest = computed(() => props.samples.at(-1))
const temperatures = computed(() =>
  props.samples.filter((s) => s.temperature !== null),
)
const points = computed(() => {
  const values = temperatures.value.map((s) => Number(s.temperature))
  const low = Math.min(props.minimum, ...values) - 1,
    high = Math.max(props.maximum, ...values) + 1
  return values
    .map(
      (v, i) =>
        20 +
        (i / Math.max(values.length - 1, 1)) * 560 +
        ',' +
        (150 - ((v - low) / (high - low)) * 130),
    )
    .join(' ')
})
const mapUrl = computed(() => {
  const sample = latest.value
  if (
    !sample ||
    !Number.isFinite(Number(sample.latitude)) ||
    !Number.isFinite(Number(sample.longitude))
  )
    return ''
  const lat = Number(sample.latitude),
    lng = Number(sample.longitude)
  return (
    'https://www.openstreetmap.org/export/embed.html?bbox=' +
    [lng - 0.025, lat - 0.025, lng + 0.025, lat + 0.025].join(',') +
    '&layer=mapnik&marker=' +
    lat +
    ',' +
    lng
  )
})
</script>
<template>
  <section class="live-panel">
    <h2 class="text-xl font-semibold">Suhu dan lokasi kiriman</h2>
    <p class="text-on-surface-variant mt-2">
      Sampel tersimpan · rentang {{ minimum }}–{{ maximum }} °C
    </p>
    <p v-if="!samples.length" class="py-6">
      Belum ada data sensor atau GPS untuk resi ini.
    </p>
    <template v-else>
      <p class="mt-3">
        {{ dateText(latest?.recorded_at) }}
        <span v-if="latest?.is_simulated">· Simulasi</span>
      </p>
      <svg
        v-if="temperatures.length"
        viewBox="0 0 600 170"
        class="w-full h-40 mt-4"
        role="img"
        aria-label="Perubahan suhu dari sampel tersimpan"
      >
        <polyline
          :points="points"
          fill="none"
          stroke="currentColor"
          stroke-width="3"
          class="text-primary"
        />
      </svg>
      <details class="mt-4">
        <summary class="cursor-pointer font-semibold py-3">
          Lihat titik GPS pada peta
        </summary>
        <iframe
          v-if="mapUrl"
          :src="mapUrl"
          title="Titik GPS terakhir kiriman di OpenStreetMap"
          class="w-full h-72 rounded-xl border-0"
          loading="lazy"
          referrerpolicy="no-referrer"
        />
        <p class="mt-2 text-on-surface-variant">
          Titik berasal dari sampel yang dikirim perangkat. Bukan pelacakan GPS
          terus-menerus.
        </p>
      </details>
      <details class="mt-4">
        <summary class="cursor-pointer py-3">
          Lihat {{ samples.length }} sampel terakhir
        </summary>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Suhu</th>
                <th>Koordinat</th>
                <th>Sumber</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="sample in [...samples].reverse()"
                :key="sample.id"
                class="border-t border-outline/15"
              >
                <td>{{ dateText(sample.recorded_at) }}</td>
                <td>{{ sample.temperature ?? '—' }} °C</td>
                <td>{{ sample.latitude }}, {{ sample.longitude }}</td>
                <td>{{ sample.is_simulated ? 'Simulasi' : 'Perangkat' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </details>
    </template>
  </section>
</template>

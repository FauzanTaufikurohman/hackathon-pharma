<script setup lang="ts">
const emit = defineEmits<{
  (e: 'view-sensor-detail'): void
  (e: 'send-wa-alert'): void
}>()
</script>

<template>
  <div class="relative overflow-hidden rounded-xl bg-tertiary-fixed/40 border border-tertiary-fixed p-4 md:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div class="flex items-start gap-3.5">
        <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-tertiary text-on-tertiary shrink-0 shadow-sm">
          <span class="material-symbols-outlined text-2xl">warning</span>
          <span class="absolute -top-1 -right-1 flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-tertiary-container opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-tertiary"></span>
          </span>
        </div>
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-label-sm text-label-sm uppercase tracking-wider bg-tertiary-container text-on-tertiary-container px-2.5 py-0.5 rounded-full font-bold">
              AI PREDICTIVE ANOMALY ENGINE • DETEKSI DINI
            </span>
            <span class="font-label-sm text-label-sm text-on-tertiary-fixed-variant font-semibold">
              Tingkat Keyakinan: 94.2%
            </span>
          </div>
          <p class="font-body-md text-body-md text-on-surface leading-relaxed">
            <strong>Peringatan Prediktif (Resi PHA-260102 - Reagen PCR):</strong> Terdeteksi tren kenaikan suhu mikro
            <span class="text-error font-semibold">+0.4°C/10 menit</span> (Suhu saat ini: <strong>7.2°C</strong>, mendekati ambang batas kritis 8.0°C).
            <em>Rekomendasi AI: Segera instruksikan kurir Bambang untuk memeriksa kerapatan kompartemen pendingin.</em>
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2.5 shrink-0 pl-12 lg:pl-0">
        <button
          type="button"
          @click="emit('view-sensor-detail')"
          class="px-3.5 py-2 rounded-lg bg-surface-container-lowest text-on-surface font-label-md text-label-md border border-outline-variant hover:bg-surface-container-low transition-colors flex items-center gap-1.5 shadow-sm cursor-pointer"
        >
          <span class="material-symbols-outlined text-lg">query_stats</span>
          Detail Sensor
        </button>
        <button
          type="button"
          @click="emit('send-wa-alert')"
          class="px-4 py-2 rounded-lg bg-tertiary text-on-tertiary font-label-md text-label-md hover:bg-tertiary-container transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer font-semibold"
        >
          <span class="material-symbols-outlined text-lg">chat</span>
          Kirim Peringatan WA ke Kurir
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { CourierTaskItem } from '../../types/shipment'

interface Props {
  task: CourierTaskItem
}

defineProps<Props>()
</script>

<template>
  <div class="bg-surface-container-lowest p-6 rounded-xl shadow-xs flex flex-col justify-between space-y-6 border border-surface-container/60">
    <div>
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2 font-bold">
          <span class="material-symbols-outlined text-primary text-[22px]">clinical_notes</span>
          Spesifikasi Muatan &amp; CPOB
        </h2>
        <span class="font-label-sm text-label-sm bg-primary-fixed text-on-primary-fixed-variant px-2.5 py-0.5 rounded font-mono font-bold shadow-2xs">
          BATCH: {{ task.batch_number ? task.batch_number.replace('#', '') : 'MN-2026-X8' }}
        </span>
      </div>

      <div class="space-y-4">
        <!-- 1. Muatan -->
        <div class="p-3.5 bg-surface-container-low rounded-xl border border-surface-container/40">
          <span class="font-label-sm text-label-sm font-mono text-on-surface-variant uppercase block font-bold">
            Komoditas &amp; Kemasan (item_summary)
          </span>
          <div class="font-body-lg text-body-lg font-bold text-on-surface mt-0.5">
            {{ task.commodity_name || 'Vaksin Meningococcal Konjugat (MenACWY)' }}
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
            {{ task.weight_str || '2 Koli (Insulated Shipper Box Khusus Vaksin, 4.8 kg)' }}
          </p>
        </div>

        <!-- 2. Profil Suhu & Toleransi -->
        <div class="p-3.5 bg-surface-container-low rounded-xl space-y-2 border border-surface-container/40">
          <div class="flex items-center justify-between">
            <span class="font-label-sm text-label-sm font-mono text-on-surface-variant uppercase font-bold">
              Rentang Suhu Terkontrol (CPOB GDP)
            </span>
            <span class="font-label-md text-label-md text-secondary font-mono font-bold">
              {{ task.temp_min ? `${task.temp_min.toFixed(1)}°C` : '2.0°C' }} – {{ task.temp_max ? `${task.temp_max.toFixed(1)}°C` : '8.0°C' }}
            </span>
          </div>

          <!-- Graphic Range Bar representation -->
          <div class="w-full bg-surface-container rounded-full h-3 relative overflow-hidden flex shadow-inner">
            <div class="w-1/4 bg-error-container" title="Zona Beku (<2°C)"></div>
            <div class="w-2/4 bg-secondary-fixed relative flex items-center justify-center" title="Zona Aman CDOB (2-8°C)">
              <div class="w-2 h-2 rounded-full bg-secondary shadow-md animate-ping"></div>
              <div class="absolute w-2 h-2 rounded-full bg-secondary shadow-md"></div>
            </div>
            <div class="w-1/4 bg-error-container" title="Zona Rusak (>8°C)"></div>
          </div>

          <div class="flex justify-between font-label-sm text-label-sm font-mono text-on-surface-variant">
            <span>0.0°C (Beku)</span>
            <span class="text-secondary font-bold">Titik Aktual: {{ task.temp_actual ? `${task.temp_actual.toFixed(1)}°C` : '4.2°C' }}</span>
            <span>10.0°C (Rusak)</span>
          </div>
        </div>

        <!-- 3. Instruksi Khusus -->
        <div class="p-3.5 bg-surface-container-low rounded-xl border border-surface-container/40">
          <span class="font-label-sm text-label-sm font-mono text-on-surface-variant uppercase block font-bold">
            Instruksi Penanganan Khusus (handling_instructions)
          </span>
          <div class="flex items-start gap-2.5 mt-1.5">
            <span class="material-symbols-outlined text-primary shrink-0 text-lg">warning</span>
            <p class="font-body-md text-body-md text-on-surface leading-snug">
              {{ task.handling_instructions || 'Wajib menggunakan Insulated Cooler Box aktif dengan 4 blok icepack PCM chilled. Dilarang membalik posisi boks (tanda panah selalu menghadap atas), hindari guncangan ekstrem dan sinar matahari langsung pada unit boks motor Satria.' }}
            </p>
          </div>
        </div>

        <!-- 4. Petugas Gudang (Inspector) -->
        <div class="p-3.5 bg-surface-container-low rounded-xl flex items-center justify-between border border-surface-container/40">
          <div>
            <span class="font-label-sm text-label-sm font-mono text-on-surface-variant uppercase block font-bold">
              Pemeriksa Cold Hub (Inspector)
            </span>
            <div class="font-label-lg text-label-lg text-on-surface font-semibold">
              {{ task.inspector_name || 'Hendra Setiawan' }} • ID: {{ task.inspector_id_code || 'CHK-092' }}
            </div>
            <div class="font-body-sm text-body-sm text-secondary font-medium">
              {{ task.inspector_status || 'Lolos Verifikasi QC Fisik & Kalibrasi Sensor' }}
            </div>
          </div>
          <div class="w-9 h-9 rounded-full bg-surface-container flex items-center justify-center text-secondary shadow-2xs">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

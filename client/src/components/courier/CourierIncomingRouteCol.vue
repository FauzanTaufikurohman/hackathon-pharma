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
          <span class="material-symbols-outlined text-primary text-[22px]">alt_route</span>
          Rute &amp; Lokasi Pengiriman
        </h2>
        <span class="font-label-sm text-label-sm font-mono text-on-surface-variant font-bold bg-surface-container px-2 py-0.5 rounded">
          {{ task.route_eta_str || 'ETA: 42 MENIT' }}
        </span>
      </div>

      <!-- Route Stepper -->
      <div class="space-y-6 relative pl-6 before:content-[''] before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-surface-container-high">
        <!-- Asal (Origin) -->
        <div class="relative">
          <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-secondary-fixed ring-4 ring-surface-container-lowest shadow-2xs"></span>
          <div class="font-label-sm text-label-sm font-mono text-secondary uppercase font-bold">
            Origin (origin_city)
          </div>
          <div class="font-body-lg text-body-lg font-bold text-on-surface">
            {{ task.origin_name || 'Hub Gudang Cawang, Jakarta Timur' }}
          </div>
          <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">
            {{ task.origin_detail || 'Staging Cold Room Area #02 (Pintu Muat C-4, Dermaga Dingin)' }}
          </p>
        </div>

        <!-- Tujuan (Destination) -->
        <div class="relative">
          <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-primary ring-4 ring-surface-container-lowest shadow-2xs"></span>
          <div class="font-label-sm text-label-sm font-mono text-primary uppercase font-bold">
            Destination (recipient_name &amp; address)
          </div>
          <div class="font-body-lg text-body-lg font-bold text-on-surface">
            {{ task.destination_name || 'RS Medika Sehat Jakarta' }}
          </div>
          <p class="font-body-md text-body-md text-on-surface font-medium">
            {{ task.destination_detail || task.destination_address || 'Jl. Raya Salemba No. 4, Jakarta Pusat' }}
          </p>
          <div class="mt-1.5 p-2.5 bg-surface-container-low rounded-lg inline-block border border-surface-container/60">
            <span class="font-label-sm text-label-sm text-on-surface-variant font-mono block">
              Titik Serah Terima Presisi:
            </span>
            <span class="font-label-md text-label-md text-primary font-semibold">
              {{ task.handover_person || 'Instalasi Farmasi Khusus Vaksin Gd. B Lt. 1 (Apt. Nurul Hidayah, S.Farm)' }}
            </span>
          </div>
        </div>

        <!-- Jadwal Tiba (Target Waktu) -->
        <div class="relative">
          <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-tertiary ring-4 ring-surface-container-lowest shadow-2xs"></span>
          <div class="font-label-sm text-label-sm font-mono text-tertiary uppercase font-bold">
            Target Waktu (scheduled_at)
          </div>
          <div class="font-body-lg text-body-lg font-bold text-on-surface">
            {{ task.scheduled_at_str || 'Hari ini, 14 Okt 2026 • Maks 15:30 WIB' }}
          </div>
          <span class="inline-flex items-center gap-1 text-xs text-error font-semibold mt-0.5">
            <span class="material-symbols-outlined text-xs">schedule</span>
            {{ task.sla_warning || 'SLA Kritis Rumah Sakit: Tidak boleh terlambat > 10 menit' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Mini Route Snapshot Map -->
    <div class="w-full h-36 rounded-xl overflow-hidden relative shadow-inner border border-surface-container">
      <div class="w-full h-full bg-surface-container flex items-center justify-center relative">
        <img
          class="w-full h-full object-cover"
          alt="Peta Rute Farmasi Cold Chain Hub Cawang ke RS Medika Sehat"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuCk7UZxFaF25f3C8toOLb_iJhh3eUwiaE3mvdrR1qtdg8pgh93tc0zjqqxPdVvRJIupcf070kfpOM01Z8OvQhQZhkcHAeT1EiLVeh65Ys49Zj6UcBnhLbUp1IJNAYG-pqPzn9wlQ1OnRRqUGmSBsH0BfuVSYbaar69mOIO-UQVSJoXNpO4WWKkaUt0wJnhEY2ILujm2vI2ew6Lu1IMIMu57zkCLW3BTzUJKejhE2Pmx7dzMt7tMfAsx9A"
          loading="lazy"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-on-background/80 via-transparent to-transparent flex items-end p-3">
          <div class="flex items-center justify-between w-full text-white">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary-fixed text-sm">navigation</span>
              <span class="font-label-md text-label-md font-medium">
                {{ task.route_traffic_note || 'Jalur Prioritas Bebas Macet (Via Tol Cawang - Pramuka)' }}
              </span>
            </div>
            <span class="font-label-sm text-label-sm bg-black/40 px-2 py-0.5 rounded backdrop-blur-sm font-mono font-bold">
              {{ task.route_distance_km ? `${task.route_distance_km} km` : '11.4 km' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

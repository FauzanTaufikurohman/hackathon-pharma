<script setup lang="ts">
// Chronological Chain of Custody Audit Log
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm space-y-4 border border-surface-container/60">
    <div class="flex items-center justify-between">
      <div>
        <span class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider block">
          Log Audit Peristiwa
        </span>
        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mt-0.5">
          Riwayat Kronologis (Chain of Custody)
        </h3>
      </div>
      <span class="text-xs font-mono bg-surface-container px-2 py-1 rounded text-on-surface-variant font-bold shadow-2xs">
        4 Events
      </span>
    </div>

    <div class="space-y-3 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-surface-container-high">
      <!-- Event 1: Delivered -->
      <div class="relative flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-secondary text-on-secondary flex items-center justify-center shrink-0 ring-4 ring-surface-container-lowest z-10 shadow-2xs">
          <span class="material-symbols-outlined text-sm font-bold">check</span>
        </div>
        <div class="flex-1 min-w-0 bg-surface-container-low p-3 rounded-xl border border-surface-container/40">
          <div class="flex items-center justify-between gap-2">
            <span class="font-label-sm text-label-sm font-bold text-secondary uppercase">
              15:18 WIB • deliver
            </span>
            <span class="font-mono text-xs text-secondary font-bold">4.4°C</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface mt-0.5">
            Kargo diserahkan sah ke APJ Apt. Siti Rahma di Farmasi RS Medika Sehat.
          </p>
        </div>
      </div>

      <!-- Event 2: Arrive -->
      <div class="relative flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0 ring-4 ring-surface-container-lowest z-10 shadow-2xs">
          <span class="material-symbols-outlined text-sm">location_on</span>
        </div>
        <div class="flex-1 min-w-0 bg-surface-container-low p-3 rounded-xl border border-surface-container/40">
          <div class="flex items-center justify-between gap-2">
            <span class="font-label-sm text-label-sm font-bold text-on-surface-variant uppercase">
              15:05 WIB • arrive
            </span>
            <span class="font-mono text-xs text-on-surface-variant font-bold">4.5°C</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface mt-0.5">
            Tiba di area drop-off Instalasi Farmasi RS Medika Sehat.
          </p>
        </div>
      </div>

      <!-- Event 3: Depart -->
      <div class="relative flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0 ring-4 ring-surface-container-lowest z-10 shadow-2xs">
          <span class="material-symbols-outlined text-sm">local_shipping</span>
        </div>
        <div class="flex-1 min-w-0 bg-surface-container-low p-3 rounded-xl border border-surface-container/40">
          <div class="flex items-center justify-between gap-2">
            <span class="font-label-sm text-label-sm font-bold text-on-surface-variant uppercase">
              13:45 WIB • depart
            </span>
            <span class="font-mono text-xs text-on-surface-variant font-bold">4.3°C</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface mt-0.5">
            Berangkat dari Hub Cawang via Tol Dalam Kota. Kotak pendingin aktif.
          </p>
        </div>
      </div>

      <!-- Event 4: Accept -->
      <div class="relative flex items-start gap-4">
        <div class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0 ring-4 ring-surface-container-lowest z-10 shadow-2xs">
          <span class="material-symbols-outlined text-sm">assignment_turned_in</span>
        </div>
        <div class="flex-1 min-w-0 bg-surface-container-low p-3 rounded-xl border border-surface-container/40">
          <div class="flex items-center justify-between gap-2">
            <span class="font-label-sm text-label-sm font-bold text-on-surface-variant uppercase">
              12:45 WIB • accept
            </span>
            <span class="font-mono text-xs text-on-surface-variant font-bold">4.2°C</span>
          </div>
          <p class="font-body-sm text-body-sm text-on-surface mt-0.5">
            Kurir Bambang menerima penugasan &amp; validasi pra-pendinginan.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

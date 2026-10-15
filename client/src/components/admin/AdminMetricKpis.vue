<script setup lang="ts">
interface Props {
  totalActive?: number
  pendingApproval?: number
  inTransit?: number
  held?: number
}

withDefaults(defineProps<Props>(), {
  totalActive: 18,
  pendingApproval: 2,
  inTransit: 9,
  held: 1,
})
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-gutter mb-space-xl">
    <!-- Card 1: Total Pengiriman Aktif -->
    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow border border-surface-container/60">
      <div class="flex items-start justify-between">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant block mb-1 font-medium">
            Total Pengiriman Aktif
          </span>
          <div class="flex items-baseline gap-2">
            <span class="font-telemetry-value text-telemetry-value text-on-surface font-bold">
              {{ totalActive }}
            </span>
            <span class="font-label-md text-label-md text-on-surface-variant">Resi</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-on-surface-variant shadow-2xs">
          <span class="material-symbols-outlined">inventory_2</span>
        </div>
      </div>
      <p class="font-body-sm text-body-sm text-on-surface-variant mt-3 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
        Kargo cold-chain &amp; ambient dalam sistem
      </p>
    </div>

    <!-- Card 2: Menunggu Approval Admin -->
    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow border border-surface-container/60">
      <div class="absolute top-0 left-0 right-0 h-1 bg-primary"></div>
      <div class="flex items-start justify-between">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold block mb-1">
            Menunggu Approval Admin
          </span>
          <div class="flex items-baseline gap-2">
            <span class="font-telemetry-value text-telemetry-value text-primary font-bold">
              {{ pendingApproval }}
            </span>
            <span class="font-label-md text-label-md text-primary font-semibold">Resi (pending_approval)</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center text-primary shadow-2xs">
          <span class="material-symbols-outlined">verified_user</span>
        </div>
      </div>
      <p class="font-body-sm text-body-sm text-on-surface-variant mt-3 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-ping"></span>
        Initial exam checker selesai, butuh approval
      </p>
    </div>

    <!-- Card 3: Dalam Perjalanan -->
    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow border border-surface-container/60">
      <div class="flex items-start justify-between">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-bold block mb-1">
            Dalam Perjalanan
          </span>
          <div class="flex items-baseline gap-2">
            <span class="font-telemetry-value text-telemetry-value text-secondary font-bold">
              {{ inTransit }}
            </span>
            <span class="font-label-md text-label-md text-secondary font-semibold">Resi (in_transit)</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-secondary-fixed/50 flex items-center justify-center text-secondary shadow-2xs">
          <span class="material-symbols-outlined">local_shipping</span>
        </div>
      </div>
      <p class="font-body-sm text-body-sm text-on-surface-variant mt-3 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
        Armada Satria aktif di rute pengantaran
      </p>
    </div>

    <!-- Card 4: Ditahan / Kendala -->
    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow border border-surface-container/60">
      <div class="flex items-start justify-between">
        <div>
          <span class="font-label-sm text-label-sm uppercase tracking-wider text-error font-bold block mb-1">
            Ditahan / Kendala
          </span>
          <div class="flex items-baseline gap-2">
            <span class="font-telemetry-value text-telemetry-value text-error font-bold">
              {{ held }}
            </span>
            <span class="font-label-md text-label-md text-error font-semibold">Resi (held)</span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-lg bg-error-container flex items-center justify-center text-error shadow-2xs">
          <span class="material-symbols-outlined">warning</span>
        </div>
      </div>
      <p class="font-body-sm text-body-sm text-on-surface-variant mt-3 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
        Butuh keputusan resmi Admin segera
      </p>
    </div>
  </div>
</template>

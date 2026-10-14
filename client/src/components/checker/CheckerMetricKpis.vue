<script setup lang="ts">
interface Props {
  waitingInspectionCount?: number
  pendingApprovalCount?: number
  passedCount?: number
}

withDefaults(defineProps<Props>(), {
  waitingInspectionCount: 3,
  pendingApprovalCount: 2,
  passedCount: 14,
})
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: Urgent Waiting Inspection -->
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-md relative overflow-hidden flex flex-col justify-between border border-surface-container/60">
      <div class="absolute top-0 left-0 right-0 h-1 bg-primary-container"></div>
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="font-label-md text-label-md text-on-surface-variant font-medium">
            Menunggu Pemeriksaan
          </span>
          <span class="bg-primary-fixed text-primary font-label-sm text-label-sm px-2.5 py-0.5 rounded-full font-bold shadow-2xs">
            Prioritas Tinggi
          </span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="font-display-lg text-display-lg text-on-surface tracking-tight font-bold">
            {{ waitingInspectionCount }}
          </span>
          <span class="font-headline-sm text-headline-sm text-on-surface-variant font-medium">
            Paket
          </span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 font-mono">
          status: <span class="font-label-sm text-label-sm text-primary font-bold">inspection</span>
        </p>
      </div>

      <div class="mt-4 pt-3 flex items-center justify-between bg-surface-container-low px-3 py-2 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">
          Batas Rilis Kritis
        </span>
        <span class="font-label-md text-label-md text-primary font-bold">
          &le; 25 Menit
        </span>
      </div>
    </div>

    <!-- Card 2: Pending Admin Approval -->
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between border border-surface-container/60">
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="font-label-md text-label-md text-on-surface-variant font-medium">
            Menunggu Persetujuan Admin
          </span>
          <span class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm px-2.5 py-0.5 rounded-full font-bold shadow-2xs">
            QC Selesai
          </span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="font-display-lg text-display-lg text-on-surface tracking-tight font-bold">
            {{ pendingApprovalCount }}
          </span>
          <span class="font-headline-sm text-headline-sm text-on-surface-variant font-medium">
            Paket
          </span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 font-mono">
          status: <span class="font-label-sm text-label-sm text-on-surface font-semibold">pending_approval</span>
        </p>
      </div>

      <div class="mt-4 pt-3 flex items-center justify-between bg-surface-container-low px-3 py-2 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">
          Workflow State
        </span>
        <span class="font-label-md text-label-md text-on-surface font-semibold">
          Gate Supervisor
        </span>
      </div>
    </div>

    <!-- Card 3: Cleared Today -->
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between border border-surface-container/60">
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="font-label-md text-label-md text-on-surface-variant font-medium">
            Selesai Diperiksa Hari Ini
          </span>
          <span class="bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm px-2.5 py-0.5 rounded-full flex items-center gap-1 font-bold shadow-2xs">
            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
            100% Lolos Uji
          </span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="font-display-lg text-display-lg text-secondary tracking-tight font-bold font-mono">
            {{ passedCount }}
          </span>
          <span class="font-headline-sm text-headline-sm text-on-surface-variant font-medium">
            Paket
          </span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 font-mono">
          status: <span class="font-label-sm text-label-sm text-secondary font-bold">passed</span>
        </p>
      </div>

      <div class="mt-4 pt-3 flex items-center justify-between bg-surface-container-low px-3 py-2 rounded-lg border border-surface-container/40">
        <span class="font-label-sm text-label-sm text-on-surface-variant font-medium">
          Kepatuhan GDP &amp; CPOB
        </span>
        <span class="font-label-md text-label-md text-secondary font-bold">
          Terpenuhi (Audit Trail Ready)
        </span>
      </div>
    </div>
  </div>
</template>

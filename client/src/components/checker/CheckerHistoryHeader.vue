<script setup lang="ts">
interface Props {
  totalTickets?: number
  pendingReview?: number
  approvalRate?: string
}

withDefaults(defineProps<Props>(), {
  totalTickets: 16,
  pendingReview: 2,
  approvalRate: '93.8%',
})
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8">
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-full bg-surface-container-high text-primary font-label-sm text-label-sm uppercase tracking-wider font-bold">
          FRD-02 Verifikasi Mutu
        </span>
        <span class="flex h-2 w-2 rounded-full bg-secondary"></span>
        <span class="font-body-sm text-body-sm text-on-surface-variant">
          Sinkronisasi Real-time QA Control Tower
        </span>
      </div>
      <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight font-bold">
        Riwayat Pemeriksaan &amp; Status Persetujuan Admin
      </h1>
      <p class="font-body-md text-body-md text-on-surface-variant max-w-3xl leading-relaxed">
        Pantau keputusan gatekeeper Admin QA Control Tower terhadap seluruh hasil Initial Exam yang telah Anda kirimkan. Setiap perubahan status dicatat secara kriptografis ke dalam log audit CDOB.
      </p>
    </div>

    <!-- Quick Stats Telemetry Cluster -->
    <div class="flex items-center gap-3 bg-surface-container-lowest p-3 rounded-xl shadow-sm self-start md:self-auto border border-surface-container/60">
      <div class="px-3 py-1.5 rounded-lg bg-surface-container-low text-left border border-surface-container/40">
        <div class="font-label-sm text-label-sm text-on-surface-variant uppercase font-medium">Total Tiket</div>
        <div class="font-headline-md text-headline-md text-on-surface font-bold">{{ totalTickets }}</div>
      </div>
      <div class="px-3 py-1.5 rounded-lg bg-amber-50 text-left border border-amber-100">
        <div class="font-label-sm text-label-sm text-tertiary uppercase font-medium">Pending Review</div>
        <div class="font-headline-md text-headline-md text-tertiary font-bold">{{ pendingReview }}</div>
      </div>
      <div class="px-3 py-1.5 rounded-lg bg-secondary-container/40 text-left border border-secondary-container/60">
        <div class="font-label-sm text-label-sm text-secondary uppercase font-medium">Approval Rate</div>
        <div class="font-headline-md text-headline-md text-secondary font-bold">{{ approvalRate }}</div>
      </div>
    </div>
  </div>
</template>

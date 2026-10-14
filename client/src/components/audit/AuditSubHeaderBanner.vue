<script setup lang="ts">
interface Props {
  trackingNumber: string
  recipientName: string
  deliveredTime?: string
  finalTemperature?: number
  auditTransactionId?: string
}

withDefaults(defineProps<Props>(), {
  deliveredTime: '14 Okt 2026, 15:18 WIB',
  finalTemperature: 4.4,
  auditTransactionId: 'TXN-8829-CP-20261014',
})
</script>

<template>
  <section class="relative w-full rounded-xl bg-secondary text-on-secondary p-space-lg shadow-md overflow-hidden mb-8">
    <!-- Big decorative watermark -->
    <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none select-none">
      <span class="material-symbols-outlined text-[180px]">verified</span>
    </div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-space-md">
      <div class="flex items-start gap-space-md">
        <!-- Verification icon badge -->
        <div class="w-12 h-12 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0 shadow-sm">
          <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">task_alt</span>
        </div>

        <div>
          <!-- Status pill & Completed timestamp -->
          <div class="flex flex-wrap items-center gap-space-sm mb-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm uppercase tracking-wide font-bold">
              <span class="w-2 h-2 rounded-full bg-secondary"></span>
              Status: Delivered
            </span>
            <span class="font-label-md text-label-md text-secondary-fixed">
              Selesai: {{ deliveredTime }}
            </span>
          </div>

          <!-- Description -->
          <p class="font-body-lg text-body-lg font-medium text-on-secondary pr-4">
            Kargo vaksin <span class="font-bold tracking-tight">#{{ trackingNumber }}</span> telah diserahterimakan dengan sukses kepada Instalasi Farmasi {{ recipientName }}.
          </p>

          <!-- Final Temperature readout -->
          <div class="flex items-center gap-2 mt-2 font-label-md text-label-md text-secondary-fixed-dim">
            <span class="material-symbols-outlined text-[16px]">thermostat</span>
            <span>Suhu akhir: <strong class="text-on-secondary">{{ finalTemperature }}°C</strong> (Profil Terjaga Sesuai Regulasi CPOB &amp; GDP)</span>
          </div>
        </div>
      </div>

      <!-- Audit Transaction ID Box -->
      <div class="shrink-0 flex items-center self-start md:self-center">
        <div class="px-4 py-2.5 rounded-lg bg-surface-container-lowest/15 backdrop-blur-md text-on-secondary text-right border border-white/10">
          <div class="font-label-sm text-label-sm uppercase tracking-widest text-secondary-fixed font-bold">ID Transaksi Audit</div>
          <div class="font-mono text-body-sm font-semibold tracking-wider">{{ auditTransactionId }}</div>
        </div>
      </div>
    </div>
  </section>
</template>

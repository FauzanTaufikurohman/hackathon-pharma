<script setup lang="ts">
// Top Notice Banner: Cold-Chain BPOM Protocol Alert
</script>

<template>
  <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-error-container/80 via-error-container to-surface-container-high p-5 shadow-sm border border-error/20">
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-error flex items-center justify-center text-on-error shrink-0 shadow-sm">
          <span class="material-symbols-outlined text-[24px]">gavel</span>
        </div>
        <div class="space-y-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-headline-sm text-headline-sm text-on-error-container font-bold">
              Protokol Karantina &amp; Insiden Cold-Chain BPOM CDOB
            </span>
            <span class="px-2 py-0.5 rounded-full bg-error text-on-error font-label-sm text-label-sm uppercase tracking-wide font-bold">
              Kepatuhan Wajib
            </span>
          </div>
          <p class="font-body-md text-body-md text-on-surface-variant max-w-4xl">
            Pelaporan kendala otomatis mengunci pengiriman ke status <span class="font-bold text-error">"HELD"</span> dan memberitahu Admin QA Control Tower. Kurir dilarang menyerahkan kargo hingga ada keputusan resmi Admin.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0 self-end md:self-center bg-surface-container-lowest/80 backdrop-blur-sm px-3.5 py-2 rounded-lg shadow-2xs border border-surface-container/60">
        <span class="w-2.5 h-2.5 rounded-full bg-error animate-ping"></span>
        <span class="font-label-md text-label-md text-on-surface font-semibold tracking-wide">
          ISO 9001 / CDOB 2024
        </span>
      </div>
    </div>
  </div>
</template>

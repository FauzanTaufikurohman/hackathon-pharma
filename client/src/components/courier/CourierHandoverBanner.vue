<script setup lang="ts">
interface Props {
  awb: string
  completionTime?: string
}

withDefaults(defineProps<Props>(), {
  completionTime: '14 Okt 2026, 15:18 WIB',
})
</script>

<template>
  <div class="bg-secondary text-on-secondary rounded-xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 border border-secondary-fixed/20">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">
          verified
        </span>
      </div>
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-headline-sm text-headline-sm font-bold tracking-tight">
            AWB: {{ awb }}
          </span>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-secondary-container text-on-secondary-container uppercase tracking-wide shadow-2xs">
            DELIVERED
          </span>
        </div>
        <p class="font-body-md text-body-md text-secondary-fixed-dim mt-0.5">
          Pengantaran Berhasil Diselesaikan • Validasi Audit Rantai Dingin Terpenuhi
        </p>
      </div>
    </div>

    <div class="flex items-center gap-3 bg-on-secondary-fixed-variant/40 px-4 py-2.5 rounded-lg border border-secondary-fixed-dim/30 shadow-2xs shrink-0 self-end md:self-center">
      <span class="material-symbols-outlined text-secondary-fixed text-xl">schedule</span>
      <div class="text-right sm:text-left">
        <div class="font-label-sm text-label-sm uppercase tracking-wider text-secondary-fixed-dim font-bold">
          Waktu Penyelesaian
        </div>
        <div class="font-label-lg text-label-lg font-bold">
          {{ completionTime }}
        </div>
      </div>
    </div>
  </div>
</template>

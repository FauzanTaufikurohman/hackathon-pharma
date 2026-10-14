<script setup lang="ts">
const emit = defineEmits<{
  (e: 'open-issue-form'): void
}>()
</script>

<template>
  <div class="bg-tertiary-fixed/30 rounded-xl p-5 shadow-sm space-y-3 border border-tertiary/20">
    <!-- Top Warning Header -->
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-full bg-tertiary-container text-on-tertiary-container flex items-center justify-center shrink-0 shadow-2xs">
        <span class="material-symbols-outlined text-[20px]">warning</span>
      </div>
      <div>
        <h4 class="font-headline-sm text-headline-sm font-bold text-on-tertiary-fixed">
          Kendala Selama Perjalanan?
        </h4>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
          Jika terjadi perubahan suhu mendadak (&gt;8.0°C), segel thermal rusak, kemasan basah, atau insiden lalu lintas.
        </p>
      </div>
    </div>

    <!-- Trigger Action Button -->
    <button
      type="button"
      @click="emit('open-issue-form')"
      class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-surface-container-lowest hover:bg-error hover:text-on-error text-error font-label-md text-label-md font-bold rounded-lg shadow-sm transition-all group cursor-pointer border border-error/20 active:scale-95"
    >
      <span class="material-symbols-outlined text-[18px]">report_problem</span>
      <span>Buka Form Lapor Kendala</span>
      <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
    </button>
  </div>
</template>

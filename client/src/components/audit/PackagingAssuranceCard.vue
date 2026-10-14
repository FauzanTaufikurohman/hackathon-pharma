<script setup lang="ts">
interface Props {
  coolerBoxId?: string
  description?: string
}

withDefaults(defineProps<Props>(), {
  coolerBoxId: 'Cooler Box V-09',
  description: 'Cooler Box V-09 diserahkan dalam keadaan tersegel micro-barcode tanpa insiden deforstasi.',
})
</script>

<template>
  <section class="rounded-xl bg-surface-container-low p-5 flex items-center gap-4 border border-surface-container">
    <div class="w-10 h-10 rounded-lg bg-primary-fixed text-primary flex items-center justify-center shrink-0 shadow-2xs">
      <span class="material-symbols-outlined text-[22px]">inventory_2</span>
    </div>
    <div class="min-w-0 flex-1">
      <div class="font-label-lg text-label-lg font-bold text-on-surface truncate">
        Kemasan &amp; Smart Logger ({{ coolerBoxId }})
      </div>
      <div class="font-body-sm text-body-sm text-on-surface-variant">
        {{ description }}
      </div>
    </div>
  </section>
</template>

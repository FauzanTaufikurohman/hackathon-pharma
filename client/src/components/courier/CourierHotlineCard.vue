<script setup lang="ts">
interface Props {
  phoneNumber?: string
  phoneExtension?: string
  whatsappUrl?: string
}

withDefaults(defineProps<Props>(), {
  phoneNumber: '021-8060-9999',
  phoneExtension: 'Ext. 4 Cold-Chain',
  whatsappUrl: 'https://wa.me/6281234567890?text=Halo%20Dispatcher%20Cold-Chain,%20laporan%20kurir%20Satria%20Bambang%20Suryono%20AWB%20PHA-260104',
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm flex flex-col gap-4 border border-surface-container/60">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary-container text-[20px]">headset_mic</span>
      <h3 class="font-label-lg text-on-surface font-bold">Hotline QA &amp; Control Tower</h3>
    </div>
    <p class="font-body-sm text-on-surface-variant">
      Perlu eskalasi darurat pembukaan segel khusus atau audit excursion temperatur saat di jalan?
    </p>

    <!-- Support Center Contact Info -->
    <div class="flex flex-col gap-2 p-3 bg-surface-container-low rounded-xl border border-surface-container/60">
      <div class="flex items-center justify-between">
        <span class="font-label-sm text-outline font-semibold">Pusat Bantuan 24 Jam</span>
        <span class="font-label-sm px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed font-bold">
          Aktif
        </span>
      </div>
      <span class="font-label-lg text-on-surface tracking-tight font-bold">
        {{ phoneNumber }} <span class="font-body-sm text-on-surface-variant font-normal">({{ phoneExtension }})</span>
      </span>
    </div>

    <!-- Quick Action Buttons -->
    <div class="grid grid-cols-2 gap-2">
      <a
        class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-label-sm font-semibold transition-colors shadow-2xs"
        :href="`tel:${phoneNumber.replace(/[^0-9]/g, '')}`"
      >
        <span class="material-symbols-outlined text-[16px]">call</span>
        <span>Telepon QA</span>
      </a>
      <a
        class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-secondary-fixed/50 hover:bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm font-bold transition-colors shadow-2xs"
        :href="whatsappUrl"
        rel="noopener noreferrer"
        target="_blank"
      >
        <span class="material-symbols-outlined text-[16px]">chat</span>
        <span>WA Dispatcher</span>
      </a>
    </div>
  </div>
</template>

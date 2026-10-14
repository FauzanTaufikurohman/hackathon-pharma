<script setup lang="ts">
interface Props {
  facilityName?: string
  facilityAddress?: string
  apjName?: string
  apjSip?: string
  apiPhone?: string
}

withDefaults(defineProps<Props>(), {
  facilityName: 'RS Medika Sehat Jakarta',
  facilityAddress: 'Gedung Farmasi & Logistik Medik, Lantai 1 (Pintu Masuk Loading Dock Barat)',
  apjName: 'Apt. Siti Rahma, S.Farm',
  apjSip: 'SIP: 19880412/SIPA-DKI/2022',
  apiPhone: '0812-9842-1100',
})
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm border border-surface-container/60">
    <!-- Header Block -->
    <div class="flex items-start justify-between gap-4 mb-4">
      <div>
        <span class="font-label-sm text-label-sm font-bold uppercase tracking-wider text-on-surface-variant block">
          Detail Lokasi Tujuan
        </span>
        <h3 class="font-headline-md text-headline-md font-bold text-on-surface mt-0.5">
          {{ facilityName }}
        </h3>
        <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">
          {{ facilityAddress }}
        </p>
      </div>
      <span class="px-2.5 py-1 rounded-md bg-surface-container-high text-on-surface font-label-sm text-label-sm font-bold shadow-2xs shrink-0">
        Priority Hospital
      </span>
    </div>

    <!-- Contact APJ Box -->
    <div class="bg-surface-container-low rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border border-surface-container/40">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-primary-fixed text-primary flex items-center justify-center shrink-0 shadow-2xs">
          <span class="material-symbols-outlined text-[24px]">medical_services</span>
        </div>
        <div>
          <span class="font-label-sm text-label-sm text-on-surface-variant block font-medium">
            Apoteker Penanggung Jawab (APJ Penerima)
          </span>
          <p class="font-label-lg text-label-lg font-bold text-on-surface mt-0.5">
            {{ apjName }}
          </p>
          <p class="font-body-sm text-body-sm text-on-surface-variant font-mono mt-0.5">
            {{ apjSip }} • {{ apiPhone }}
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2 shrink-0">
        <a
          :href="`tel:${apiPhone.replace(/[^0-9]/g, '')}`"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-surface-container-highest hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-bold rounded-lg transition-colors shadow-2xs cursor-pointer active:scale-95"
        >
          <span class="material-symbols-outlined text-[18px]">call</span>
          <span>Panggil</span>
        </a>

        <a
          :href="`https://wa.me/62${apiPhone.replace(/^0/, '').replace(/[^0-9]/g, '')}?text=${encodeURIComponent('Halo Apt. Siti Rahma, saya Satria Cold-Chain Anteraja sedang menuju RS Medika Sehat membawa vaksin kargo PHA-260104.')}`"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center gap-1.5 px-4 py-2.5 bg-secondary text-on-secondary hover:bg-on-secondary-container font-label-md text-label-md font-bold rounded-lg transition-colors shadow-xs cursor-pointer active:scale-95"
        >
          <span class="material-symbols-outlined text-[18px]">chat</span>
          <span>WhatsApp APJ</span>
        </a>
      </div>
    </div>
  </div>
</template>

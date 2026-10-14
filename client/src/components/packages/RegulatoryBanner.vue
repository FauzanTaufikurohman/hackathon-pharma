<script setup lang="ts">
const emit = defineEmits<{
  (e: 'openGuide'): void
}>()
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-md sm:p-space-lg shadow-xs border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
    <div class="flex items-center gap-space-md">
      <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[28px]">shield_with_heart</span>
      </div>
      <div class="flex flex-col">
        <h4 class="font-headline-sm text-headline-sm text-on-surface font-extrabold tracking-tight">
          Prosedur Serah Terima Klinis Berstandar CDOB BPOM RI
        </h4>
        <p class="font-body-sm text-body-sm text-on-surface-variant max-w-2xl leading-relaxed mt-0.5">
          Klik kartu pengiriman mana pun di atas untuk membuka layar peninjauan spesifik: verifikasi kalibrasi sensor pra-kirim, inspeksi peta rute telemetri aktif, atau unduhan berita acara serah terima resmi (e-POD).
        </p>
      </div>
    </div>
    <div class="flex items-center gap-space-sm shrink-0 self-stretch sm:self-auto">
      <button
        type="button"
        @click="emit('openGuide')"
        class="w-full sm:w-auto text-center px-space-md py-2.5 rounded-lg bg-surface-container text-on-surface hover:text-primary font-label-md text-label-md font-semibold transition-colors cursor-pointer border border-surface-container-high"
      >
        Panduan Excursion BPOM
      </button>
    </div>
  </div>
</template>

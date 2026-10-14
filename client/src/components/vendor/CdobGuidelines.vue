<script setup lang="ts">
// Panduan Standar Penerimaan Faskes
</script>

<template>
  <div class="flex flex-col gap-space-md pt-2">
    <!-- Header -->
    <div class="flex flex-col gap-1">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[24px]">verified</span>
        <h2 class="font-headline-md text-headline-md text-on-surface font-extrabold tracking-tight">
          Panduan Standar Penerimaan Faskes (CPOB &amp; GDP)
        </h2>
      </div>
      <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
        Patuhi tiga langkah protokol penerimaan fisik rantai dingin untuk menjamin kestabilan potency obat sebelum tandatangan serah terima.
      </p>
    </div>

    <!-- 3 Steps Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
      <!-- Step 1 -->
      <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container p-space-md flex flex-col gap-3 group hover:shadow-md transition-all">
        <div class="w-10 h-10 rounded-xl bg-secondary-fixed flex items-center justify-center text-secondary shadow-2xs group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-[22px]">thermostat</span>
        </div>
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <span class="font-label-sm text-[11px] text-primary font-bold">LANGKAH 01</span>
            <span class="w-1 h-1 rounded-full bg-outline"></span>
            <span class="font-label-sm text-[11px] text-secondary font-bold">Kritis</span>
          </div>
          <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Validasi Suhu Awal</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Periksa display digital data logger pada coolbox sebelum segel dibuka. Pastikan angka berada di rentang <strong class="text-on-surface font-bold">2.0&deg;C &ndash; 8.0&deg;C</strong> tanpa ada indikator alarm deviasi termal aktif.
          </p>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container p-space-md flex flex-col gap-3 group hover:shadow-md transition-all">
        <div class="w-10 h-10 rounded-xl bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed shadow-2xs group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-[22px]">lock_reset</span>
        </div>
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <span class="font-label-sm text-[11px] text-primary font-bold">LANGKAH 02</span>
            <span class="w-1 h-1 rounded-full bg-outline"></span>
            <span class="font-label-sm text-[11px] text-on-surface-variant font-bold">Fisik</span>
          </div>
          <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Periksa Segel Tamper-Evident</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Pastikan barcode segel dan RFID tag terpasang utuh tanpa indikasi manipulasi fisik. Cocokkan nomor serial segel dengan data pada portal digital Anteraja Pharma.
          </p>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="bg-surface-container-lowest rounded-2xl shadow-xs border border-surface-container p-space-md flex flex-col gap-3 group hover:shadow-md transition-all">
        <div class="w-10 h-10 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-2xs group-hover:scale-105 transition-transform">
          <span class="material-symbols-outlined text-[22px]">draw</span>
        </div>
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <span class="font-label-sm text-[11px] text-primary font-bold">LANGKAH 03</span>
            <span class="w-1 h-1 rounded-full bg-outline"></span>
            <span class="font-label-sm text-[11px] text-on-surface-variant font-bold">Legalitas</span>
          </div>
          <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Otorisasi Digital e-POD</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Lakukan serah terima melalui verifikasi tanda tangan digital Apoteker Penanggung Jawab (APJ) faskes. Berita Acara Penerimaan (BAP) akan terbit otomatis dan tersimpan di cloud.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

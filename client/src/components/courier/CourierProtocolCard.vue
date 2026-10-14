<script setup lang="ts">
</script>

<template>
  <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm flex flex-col justify-between gap-6 border border-surface-container/60">
    <div class="flex flex-col gap-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-secondary-fixed flex items-center justify-center text-on-secondary-fixed shadow-2xs">
            <span class="material-symbols-outlined text-[18px]">verified</span>
          </div>
          <div>
            <h3 class="font-label-lg text-on-surface font-bold">Protokol CDOB &amp; CPOB</h3>
            <span class="font-label-sm text-outline font-medium">Standar Kualitas BPOM RI</span>
          </div>
        </div>
        <span class="px-2 py-0.5 rounded-full bg-secondary-fixed/40 text-secondary font-label-sm font-bold">
          Resmi
        </span>
      </div>

      <p class="font-body-sm text-on-surface-variant">
        Setiap kurir Satria Pharma wajib menegakkan integritas rantai dingin (cold-chain integrity) selama transit tanpa pengecualian.
      </p>

      <!-- 3-Point Checklist -->
      <div class="flex flex-col gap-3.5 mt-1">
        <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low border border-surface-container/40">
          <div class="w-6 h-6 rounded-full bg-secondary text-on-secondary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 shadow-2xs">
            1
          </div>
          <div class="flex flex-col">
            <span class="font-label-md text-on-surface font-bold">Suhu Cooler Box 2°C – 8°C</span>
            <span class="font-body-sm text-on-surface-variant">Pastikan probe IoT aktif dan stabil sebelum meninggalkan dermaga dingin Hub Cawang.</span>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low border border-surface-container/40">
          <div class="w-6 h-6 rounded-full bg-secondary text-on-secondary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 shadow-2xs">
            2
          </div>
          <div class="flex flex-col">
            <span class="font-label-md text-on-surface font-bold">Verifikasi Segel Fisik</span>
            <span class="font-body-sm text-on-surface-variant">Cocokkan nomor segel tamper-evident (SL-8891) di hadapan Apoteker Penanggung Jawab (APJ).</span>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low border border-surface-container/40">
          <div class="w-6 h-6 rounded-full bg-secondary text-on-secondary flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 shadow-2xs">
            3
          </div>
          <div class="flex flex-col">
            <span class="font-label-md text-on-surface font-bold">Lapor Kendala Segera</span>
            <span class="font-body-sm text-on-surface-variant">Jika ada selisih suhu &gt;8°C atau kerusakan fisik boks, segera laporkan ke Admin via tombol kendala.</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Cooler Box Visual Inspection Reference -->
    <div class="rounded-xl overflow-hidden bg-surface-container-low p-3 flex items-center gap-3 border border-surface-container/60 shadow-2xs">
      <img
        class="w-16 h-16 rounded-lg object-cover shadow-xs border border-surface-container"
        alt="Pharmaceutical cooler box"
        src="https://lh3.googleusercontent.com/aida-public/AB6AXuA0w2MBsy3kpXayn9k1kmANM9uW-cx_0kGrBbUofgh5KkAcJHUw80ZONR0Dm7Hwmj-zw34Zf4uR8aeFT49TW5qAazljEiUCP7rRkPeenhwhpfNUtatzOcWU5HeHIeQeWkrWAxmmPVRFhuL3MM26vKCVO90XCmo0JMvQQ0ucBA-QpKJ61IclEbFPI52fg-hxpwQF5l9_oS1CkF04EoJDL7dO4xdSXFafx44C9--gZQaUjSwngdjKJMEeAA"
      />
      <div class="flex flex-col">
        <span class="font-label-sm text-on-surface font-bold">Unit: PharmaBox-V2 Pro</span>
        <span class="font-body-sm text-on-surface-variant">Insulasi VIP Vacuum Panel</span>
        <span class="font-label-sm text-secondary mt-0.5 flex items-center gap-1 font-semibold">
          <span class="material-symbols-outlined text-[14px]">check</span> Siap Beroperasi
        </span>
      </div>
    </div>
  </div>
</template>

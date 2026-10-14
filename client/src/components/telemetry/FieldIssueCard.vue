<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    activeIssuesCount?: number
    qaOnDuty?: string
  }>(),
  {
    activeIssuesCount: 0,
    qaOnDuty: 'dr. Ardiansyah (Apoteker QA)',
  }
)
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-xs border border-surface-container flex flex-col gap-space-md">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-space-xs">
        <span class="material-symbols-outlined text-secondary text-[20px]">health_and_safety</span>
        <span class="font-headline-sm text-headline-sm text-on-surface font-extrabold tracking-tight">Status Kendala Lapangan</span>
      </div>
      <span class="font-label-sm text-[11px] bg-secondary-container/40 text-on-secondary-container px-2 py-0.5 rounded font-bold border border-secondary-container">
        {{ activeIssuesCount }} Aktif (resolved/none)
      </span>
    </div>

    <!-- Clean Green Status Box -->
    <div class="p-3.5 rounded-xl bg-secondary-container/15 border border-secondary-container/30 flex items-start gap-space-sm">
      <span class="material-symbols-outlined text-secondary text-[20px] shrink-0 mt-0.5">check_circle</span>
      <div class="flex flex-col">
        <span class="font-label-lg text-label-lg text-on-secondary-container font-bold">Kondisi Kargo Dingin Normal</span>
        <p class="font-body-sm text-[13px] text-on-surface-variant mt-0.5 leading-relaxed">
          Semua parameter logistik normal. Integritas segel digital dan sistem kompresor reefer terjaga 100% tanpa deviasi.
        </p>
      </div>
    </div>

    <!-- SOP Transparansi Deviasi & Resolusi Komprehensif -->
    <div class="bg-surface-container-low/60 rounded-xl p-space-md flex flex-col gap-space-xs border border-surface-container">
      <div class="flex items-center justify-between mb-1">
        <span class="font-label-sm text-[11px] uppercase text-on-surface-variant font-bold tracking-wider">
          Protokol Penanganan Deviasi
        </span>
        <span class="material-symbols-outlined text-[16px] text-on-surface-variant">info</span>
      </div>

      <div class="space-y-2 text-body-sm font-body-sm">
        <div class="flex items-start gap-2 bg-surface-container-lowest p-2 rounded-lg border border-surface-container">
          <span class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
          <div>
            <span class="font-label-md text-label-md text-on-surface font-bold">Eskalasi Suhu (&gt;8.0&deg;C / &lt;2.0&deg;C):</span>
            <p class="text-on-surface-variant text-label-sm text-[11px] mt-0.5 leading-relaxed">
              Alert otomatis ke Control Tower &rarr; Admin QA menentukan <code class="text-primary font-mono font-bold">action: return</code> atau <code class="text-secondary font-mono font-bold">resume</code>.
            </p>
          </div>
        </div>

        <div class="flex items-start gap-2 bg-surface-container-lowest p-2 rounded-lg border border-surface-container">
          <span class="w-1.5 h-1.5 rounded-full bg-tertiary mt-1.5 shrink-0"></span>
          <div>
            <span class="font-label-md text-label-md text-on-surface font-bold">Integritas Segel Fisik / Box:</span>
            <p class="text-on-surface-variant text-label-sm text-[11px] mt-0.5 leading-relaxed">
              Verifikasi digital POD langsung melalui aplikasi Satria saat serah terima di instalasi farmasi.
            </p>
          </div>
        </div>
      </div>

      <div class="mt-2 pt-2 border-t border-surface-container-high/60 flex items-center justify-between font-label-sm text-[11px] text-on-surface-variant">
        <span>QA On-Duty: <strong class="text-on-surface">{{ qaOnDuty }}</strong></span>
        <span class="text-secondary font-bold">CPOB Certified</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  awb: string
  isReturning?: boolean
}

withDefaults(defineProps<Props>(), {
  isReturning: false,
})

const emit = defineEmits<{
  (e: 'confirm-return'): void
}>()
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col gap-5 border border-surface-container/60">
    <!-- Live Status Alert Banner -->
    <div class="bg-error-container rounded-xl p-4.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-inner border border-error/30">
      <div class="flex items-center gap-3">
        <div class="w-3.5 h-3.5 rounded-full bg-error animate-ping shrink-0"></div>
        <div>
          <div class="font-headline-sm text-headline-sm text-on-error-container font-bold uppercase tracking-wide">
            STATUS SHIPMENT: {{ isReturning ? 'RETURNING' : 'HELD' }}
          </div>
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            {{ isReturning ? 'Dalam Perjalanan Kembali ke Hub Cawang' : 'Ditahan untuk Pemeriksaan QA' }} • AWB: {{ awb }}
          </p>
        </div>
      </div>
      <span class="px-3 py-1 rounded-full bg-error text-on-error font-label-sm text-label-sm font-bold tracking-wider uppercase shrink-0 shadow-2xs">
        {{ isReturning ? 'Retur Aktif' : 'Akses Dikunci' }}
      </span>
    </div>

    <!-- Advisory Note -->
    <div class="bg-surface-container-low rounded-xl p-4 flex items-start gap-3 border border-surface-container/40">
      <span class="material-symbols-outlined text-tertiary text-[20px] shrink-0 mt-0.5">hourglass_top</span>
      <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
        Menunggu instruksi lanjutan dari Dispatcher &amp; QA Lead Anteraja Pharma. Sesuai CDOB, dilarang membuka muatan atau meninggalkan titik pengantaran tanpa verifikasi.
      </p>
    </div>

    <!-- Official QA Admin Decision Card -->
    <div class="bg-surface rounded-xl p-5 shadow-sm space-y-4 border border-surface-container-high">
      <div class="flex items-center justify-between border-b pb-3 border-surface-container-high">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary-container text-[24px]">verified_user</span>
          <div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">
              Keputusan Resmi Admin QA Control Tower
            </h3>
            <p class="font-label-sm text-label-sm text-on-surface-variant font-mono">
              Mapped to: decisions table
            </p>
          </div>
        </div>
        <span class="px-2.5 py-1 rounded bg-surface-container-high font-mono text-on-surface font-label-sm text-label-sm font-bold shadow-2xs">
          DEC-2026-0941
        </span>
      </div>

      <!-- Decision Action Badge -->
      <div class="space-y-1.5">
        <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold block">
          Aksi Putusan (<code class="font-mono">decisions.action</code>):
        </span>
        <div class="flex items-center gap-3">
          <span class="px-4 py-2 rounded-lg bg-error text-on-error font-headline-sm text-headline-sm font-bold tracking-wide flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-[20px]">assignment_return</span>
            <span>RETURN (Instruksi: Retur Kargo ke Gudang Asal)</span>
          </span>
        </div>
      </div>

      <!-- Decision Reason / Justification -->
      <div class="space-y-1.5">
        <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider font-semibold block">
          Alasan Keputusan (<code class="font-mono">decisions.reason</code>):
        </span>
        <div class="bg-surface-container-lowest p-4 rounded-xl shadow-inner font-body-md text-body-md text-on-surface leading-relaxed border border-surface-container/40">
          “Segel fisik terbukti tidak utuh dan berpotensi merusak sterilitas biologis sesuai regulasi BPOM CDOB. <span class="font-bold text-error">JANGAN serahkan kargo ke pihak rumah sakit.</span> Segera kunci ulang cooler box dan bawa kembali kargo ke Hub Cawang untuk investigasi karantina.”
        </div>
      </div>

      <!-- Digital Authorization Signature -->
      <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-on-surface-variant bg-surface-container-low/50 p-3 rounded-lg border border-surface-container/40">
        <div class="flex items-center gap-2.5">
          <img
            class="w-9 h-9 rounded-full object-cover shadow-2xs ring-1 ring-primary-container/20"
            alt="dr. Ardiansyah, Apt."
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDEIlLZxb-hUUU4MDB1meeVROOug4xALin76GqHpRqBMrJCcsK6a9IRba37BLGbGlp29pQpJQSHTR5ll5YgLqwjkQZVXQxpDrhRPDTOQr4XPiyNw71yw3UOtUSqLOb8sEzIvkOLvxA_laWVU5Hx7hIPdfrCCSBF0wZf1JioCxB-JHG7PZcDH7NZUKNRP7rU94xthyKvDckl8q0s9abJ21kDC6q8qowtVW8vDmwzGpmy6wWZTILNsOxRJA"
            loading="lazy"
          />
          <div>
            <div class="font-label-md text-label-md text-on-surface font-bold">dr. Ardiansyah, Apt.</div>
            <div class="font-label-sm text-label-sm text-on-surface-variant">QA Lead &amp; Penanggung Jawab Teknis CDOB</div>
          </div>
        </div>
        <div class="text-left sm:text-right font-mono font-label-sm text-label-sm">
          <div class="text-on-surface font-semibold">14 Okt 2026, 15:22 WIB</div>
          <div class="text-secondary flex items-center sm:justify-end gap-1 font-semibold">
            <span class="material-symbols-outlined text-[14px]">lock</span>
            <span>RSA-2048 Signed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Telemetry Audit Snapshot During Hold -->
    <div class="bg-surface-container-low rounded-xl p-4 flex items-center justify-between gap-4 border border-surface-container/40">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-surface-container-highest flex items-center justify-center text-primary shrink-0 shadow-2xs">
          <span class="material-symbols-outlined text-[22px]">sensors</span>
        </div>
        <div>
          <div class="font-label-md text-label-md text-on-surface font-semibold">Sensor S-CLD-991 Aktif</div>
          <div class="font-body-sm text-body-sm text-on-surface-variant">
            Suhu Kargo Terakhir: <span class="font-mono font-bold text-secondary">3.4°C</span> (Aman dalam rentang 2–8°C)
          </div>
        </div>
      </div>
      <span class="px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-semibold shrink-0 shadow-2xs">
        Logger ON
      </span>
    </div>

    <!-- Courier Action Execution Button -->
    <div class="space-y-2 mt-auto pt-2">
      <button
        type="button"
        @click="emit('confirm-return')"
        class="w-full py-4 px-6 rounded-xl font-headline-sm text-headline-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]"
        :class="isReturning
          ? 'bg-secondary text-white'
          : 'bg-primary-container hover:bg-primary text-on-primary'"
      >
        <span class="material-symbols-outlined text-[22px]">
          {{ isReturning ? 'task_alt' : 'sync_alt' }}
        </span>
        <span>
          {{ isReturning ? 'Instruksi Retur Aktif (Menuju Hub Cawang)' : 'Konfirmasi & Jalankan Instruksi Retur (Update shipments → returning)' }}
        </span>
      </button>
      <p class="text-center font-body-sm text-body-sm text-on-surface-variant">
        Sistem akan memperbarui rute navigasi kembali ke Gudang Cawang dan otomatis menerbitkan e-Berita Acara Retur Karantina.
      </p>
    </div>
  </div>
</template>

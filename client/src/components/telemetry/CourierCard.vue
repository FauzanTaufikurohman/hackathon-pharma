<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    courierName?: string
    courierId?: string
    fleetPlate?: string
    statusText?: string
    reeferRange?: string
    scheduledAt?: string
    phoneUrl?: string
  }>(),
  {
    courierName: 'Bambang Suryono',
    courierId: 'STR-PHR-094',
    fleetPlate: 'Reefer Dedicated Box B 9482 PBF',
    statusText: 'Satria siap mengantar / Aktif di rute',
    reeferRange: '2.0°C – 8.0°C Aktif',
    scheduledAt: '14 Okt 2026, 15:30 WIB',
    phoneUrl: 'https://wa.me/6281234567890?text=Halo%20Satria%20Bambang,%20konfirmasi%20kiriman%20Vaksin%20RS%20Medika%20Sehat%20AWB%20PHA-260104',
  }
)
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-xs border border-surface-container flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-lg">
    <div class="flex items-center gap-space-md">
      <!-- Courier Avatar with CDOB Certified Badge -->
      <div class="relative shrink-0">
        <img
          class="w-16 h-16 rounded-xl object-cover shadow-xs bg-surface-container-high ring-2 ring-primary-fixed"
          src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200"
          alt="Foto Satria Kurir"
        />
        <div
          class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-secondary flex items-center justify-center text-on-secondary shadow-xs ring-2 ring-surface-container-lowest"
          title="Satria Tersertifikasi CDOB"
        >
          <span class="material-symbols-outlined text-[13px]">check</span>
        </div>
      </div>

      <!-- Courier Info -->
      <div class="flex flex-col">
        <div class="flex items-center gap-space-xs flex-wrap">
          <span class="font-headline-sm text-headline-sm text-on-surface font-extrabold">{{ courierName }}</span>
          <span class="font-label-sm text-[11px] px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant font-bold">
            {{ courierId }}
          </span>
        </div>
        <div class="text-on-surface-variant font-body-sm text-[13px] mt-0.5">
          Armada: <strong class="text-on-surface font-bold">{{ fleetPlate }}</strong>
        </div>
        <div class="flex items-center gap-2 mt-1 flex-wrap">
          <span class="inline-flex items-center gap-1 font-label-sm text-[11px] text-secondary font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
            Status: {{ statusText }}
          </span>
          <span class="text-outline-variant">&bull;</span>
          <span class="font-label-sm text-[11px] text-on-surface-variant">Suhu Reefer: {{ reeferRange }}</span>
        </div>
      </div>
    </div>

    <!-- Contact Button & ETA -->
    <div class="flex flex-col sm:items-end w-full sm:w-auto gap-space-xs shrink-0">
      <a
        :href="phoneUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-lg text-label-lg font-bold shadow-xs flex items-center justify-center gap-2 transition-transform active:scale-95 text-center cursor-pointer"
      >
        <span class="material-symbols-outlined text-[18px]">chat</span>
        <span>Hubungi Satria via WhatsApp / Telegram</span>
      </a>
      <span class="font-body-sm text-[12px] text-on-surface-variant sm:text-right">
        Jadwal Tiba: <strong class="text-on-surface font-bold">{{ scheduledAt }}</strong>
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { mockShipments } from '../../data/mockShipments'

const router = useRouter()
const filterStatus = ref<string>('all')

const filteredShipments = ref(mockShipments)

function applyFilter(status: string) {
  filterStatus.value = status
  if (status === 'all') {
    filteredShipments.value = mockShipments
  } else {
    filteredShipments.value = mockShipments.filter(s => s.status === status)
  }
}

function openDetail(awb: string) {
  router.push(`/vendor/monitor/${awb}`)
}
</script>

<template>
  <div class="w-full flex flex-col gap-space-lg pb-space-xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
      <div>
        <h1 class="font-display-lg text-[26px] sm:text-[30px] font-bold text-on-surface">
          Daftar Paket Saya
        </h1>
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Semua kargo vaksin dan obat rantai dingin yang ditujukan ke Instalasi Farmasi RS Medika Sehat
        </p>
      </div>
      <router-link
        to="/"
        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-variant text-on-surface text-label-md font-semibold transition-colors self-start sm:self-auto border border-surface-container-high"
      >
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        <span>Kembali ke Beranda</span>
      </router-link>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        @click="applyFilter('all')"
        class="px-4 py-1.5 rounded-full text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="filterStatus === 'all' ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        Semua ({{ mockShipments.length }})
      </button>
      <button
        @click="applyFilter('pending_approval')"
        class="px-4 py-1.5 rounded-full text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="filterStatus === 'pending_approval' ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        Menunggu Persetujuan (1)
      </button>
      <button
        @click="applyFilter('in_transit')"
        class="px-4 py-1.5 rounded-full text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="filterStatus === 'in_transit' ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        Dalam Perjalanan (1)
      </button>
      <button
        @click="applyFilter('completed')"
        class="px-4 py-1.5 rounded-full text-label-md font-semibold transition-all cursor-pointer whitespace-nowrap"
        :class="filterStatus === 'completed' ? 'bg-primary text-on-primary shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface'"
      >
        Selesai Diterima (1)
      </button>
    </div>

    <!-- Shipments List -->
    <div class="grid grid-cols-1 gap-space-md">
      <div
        v-for="s in filteredShipments"
        :key="s.id"
        class="bg-surface-container-lowest rounded-2xl p-space-lg border border-surface-container shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md hover:shadow-md hover:border-primary/30 transition-all cursor-pointer"
        @click="openDetail(s.tracking_number)"
      >
        <div class="flex items-start gap-space-md">
          <div class="w-12 h-12 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shrink-0 shadow-2xs">
            <span class="material-symbols-outlined text-[26px]">vaccines</span>
          </div>
          <div class="flex flex-col">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-headline-sm text-headline-sm text-on-surface font-extrabold">{{ s.tracking_number }}</span>
              <span
                class="px-2.5 py-0.5 rounded-full font-label-sm text-[11px] font-bold uppercase tracking-wider"
                :class="{
                  'bg-tertiary-fixed text-on-tertiary-fixed': s.status === 'pending_approval',
                  'bg-surface-variant text-on-surface': s.status === 'in_transit',
                  'bg-secondary-fixed text-on-secondary-fixed': s.status === 'completed'
                }"
              >
                {{ s.status === 'pending_approval' ? 'Menunggu Persetujuan' : (s.status === 'in_transit' ? 'Dalam Perjalanan' : 'Selesai') }}
              </span>
            </div>
            <p class="font-body-md text-body-md text-on-surface font-semibold mt-1">
              {{ s.item_summary }}
            </p>
            <div class="flex items-center gap-2 text-body-sm text-on-surface-variant text-[12px] mt-0.5">
              <span>Dari: {{ s.sender_name }}</span>
              <span>&bull;</span>
              <span>Suhu Terkini: <strong class="text-secondary font-bold">{{ s.current_temperature }}&deg;C</strong></span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-space-md self-end md:self-auto shrink-0">
          <div class="text-right hidden sm:flex flex-col">
            <span class="text-[11px] text-outline uppercase font-semibold">Estimasi Tiba</span>
            <span class="font-label-md text-label-md font-bold text-on-surface">15:30 WIB</span>
          </div>
          <button
            type="button"
            class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md font-semibold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer"
          >
            <span>Buka Monitor</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

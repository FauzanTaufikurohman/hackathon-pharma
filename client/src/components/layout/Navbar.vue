<script setup lang="ts">

import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { currentFacility } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

const router = useRouter()
const route = useRoute()

const isMonitorPage = computed(() => route.path.startsWith('/vendor/monitor'))

const currentAwb = computed(() => {
  const param = route.params.awb as string | undefined
  if (param) return decodeURIComponent(param)
  return '#100098421088712'
})

const activeScreen = computed(() => {
  if (!isMonitorPage.value) return 0
  return Number(route.query.screen) || 1
})

function navigateToTab(screen: number) {
  if (isMonitorPage.value) {
    router.replace({
      query: { ...route.query, screen: screen.toString() },
    })
  } else {
    router.push({
      path: `/vendor/monitor/${encodeURIComponent(currentAwb.value)}`,
      query: { screen: screen.toString() },
    })
  }
}

function handleLogout() {
  return logoutAndRedirect(router)
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-100 shadow-sm">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
      <!-- Left: Logo & Navigation Tabs -->
      <div class="flex items-center gap-5 lg:gap-8">
        <!-- Logo Anteraja Pharma -->
        <router-link
          to="/"
          class="flex items-center focus:outline-none rounded-lg shrink-0"
          title="Beranda Anteraja Pharma"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 w-auto object-contain"
            src="/pharma-logo.webp"
          />
        </router-link>

        <!-- STATE 1: Portal Navigation (Beranda & Paket Saya) for '/' or '/paket-saya' -->
        <nav v-if="!isMonitorPage" class="flex items-center gap-1.5 sm:gap-2">
          <router-link
            to="/"
            class="transition-colors px-4 py-2 rounded-full text-sm font-semibold"
            :class="route.path === '/'
              ? 'bg-[#FCE7F3] text-[#D81E5B]'
              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-50'"
          >
            Beranda
          </router-link>

          <router-link
            to="/paket-saya"
            class="transition-colors px-4 py-2 rounded-full text-sm font-semibold"
            :class="route.path === '/paket-saya'
              ? 'bg-[#FCE7F3] text-[#D81E5B]'
              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-50'"
          >
            Paket Saya
          </router-link>
        </nav>

        <!-- STATE 2: Detail Navigation (Monitor Page, Tracking GPS, Laporan) for '/vendor/monitor/:awb' -->
        <div v-else class="flex items-center gap-2 sm:gap-3">
          <!-- Back button to Paket Saya -->
          <router-link
            to="/paket-saya"
            class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-[#D81E5B] bg-slate-100 hover:bg-pink-50 px-3 py-1.5 rounded-full transition-all shrink-0"
            title="Kembali ke Daftar Paket Saya"
          >
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span class="hidden xs:inline">Paket Saya</span>
          </router-link>

          <span class="text-slate-200 text-xs hidden sm:inline">•</span>

          <!-- 3 Detail tabs -->
          <nav class="flex items-center gap-1 sm:gap-1.5">
            <button
              type="button"
              @click="navigateToTab(1)"
              title="Monitor Page (Halaman Pantau)"
              class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold cursor-pointer flex items-center gap-1.5"
              :class="activeScreen === 1
                ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs'
                : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100'"
            >
              <span v-if="activeScreen === 1" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
              <span>Monitor Page</span>
            </button>

            <button
              type="button"
              @click="navigateToTab(2)"
              title="Tracking GPS (Pelacakan GPS)"
              class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold cursor-pointer flex items-center gap-1.5"
              :class="activeScreen === 2
                ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs'
                : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100'"
            >
              <span v-if="activeScreen === 2" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
              <span>Tracking GPS</span>
            </button>

            <button
              type="button"
              @click="navigateToTab(3)"
              title="Laporan & Bukti Serah Terima"
              class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold cursor-pointer flex items-center gap-1.5"
              :class="activeScreen === 3
                ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs'
                : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100'"
            >
              <span v-if="activeScreen === 3" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
              <span>Laporan</span>
            </button>
          </nav>
        </div>
      </div>

      <!-- Right: Facility Verified Badge & Logout -->
      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200/80 shadow-xs">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="font-semibold text-xs text-slate-800">{{ currentFacility.name }}</span>
          <span class="material-symbols-outlined text-emerald-600 text-[14px]">verified</span>
        </div>

        <button
          type="button"
          @click="handleLogout"
          class="inline-flex items-center rounded-full border border-slate-200 px-3.5 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 cursor-pointer"
        >
          Keluar
        </button>
      </div>
    </div>
  </header>
</template>

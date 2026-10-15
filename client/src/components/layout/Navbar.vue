<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { currentFacility } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

const router = useRouter()
const route = useRoute()

const currentAwb = computed(() => {
  const param = route.params.awb as string | undefined
  if (param) return decodeURIComponent(param)
  return '#PHA-260104'
})

const activeScreen = computed(() => {
  if (!route.path.startsWith('/vendor/monitor')) return 0
  return Number(route.query.screen) || 1
})

function navigateToTab(screen: number) {
  if (route.path.startsWith('/vendor/monitor')) {
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
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 max-w-[1200px] mx-auto px-4 md:px-6 lg:px-8 flex items-center justify-between gap-space-md">
      <!-- Left: Logo & Navigation Tabs -->
      <div class="flex items-center gap-6 lg:gap-8">
        <!-- Logo Anteraja Pharma -->
        <router-link
          to="/"
          class="flex items-center gap-space-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg shrink-0"
          title="Beranda Anteraja Pharma"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 w-auto object-contain"
            src="/pharma-logo.webp"
          />
        </router-link>

        <!-- Navigation: 3 Layanan tabs (Halaman Pantau, Pelacakan GPS, Laporan) -->
        <nav class="flex items-center gap-1.5 sm:gap-2">
          <button
            type="button"
            @click="navigateToTab(1)"
            class="transition-colors py-1.5 px-3.5 rounded-full font-label-lg text-label-lg cursor-pointer flex items-center gap-1.5"
            :class="activeScreen === 1
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low font-medium'"
          >
            <span v-if="activeScreen === 1" class="w-1.5 h-1.5 rounded-full bg-primary"></span>
            <span>Halaman Pantau</span>
          </button>

          <button
            type="button"
            @click="navigateToTab(2)"
            class="transition-colors py-1.5 px-3.5 rounded-full font-label-lg text-label-lg cursor-pointer flex items-center gap-1.5"
            :class="activeScreen === 2
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low font-medium'"
          >
            <span v-if="activeScreen === 2" class="w-1.5 h-1.5 rounded-full bg-primary"></span>
            <span>Pelacakan GPS</span>
          </button>

          <button
            type="button"
            @click="navigateToTab(3)"
            class="transition-colors py-1.5 px-3.5 rounded-full font-label-lg text-label-lg cursor-pointer flex items-center gap-1.5"
            :class="activeScreen === 3
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low font-medium'"
          >
            <span v-if="activeScreen === 3" class="w-1.5 h-1.5 rounded-full bg-primary"></span>
            <span>Laporan</span>
          </button>
        </nav>
      </div>

      <!-- Right: Facility Verified Badge & Logout -->
      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 shadow-xs">
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

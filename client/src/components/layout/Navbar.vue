<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { currentFacility } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

const router = useRouter()
const route = useRoute()
const searchInput = ref('')

const isMonitorPage = computed(() => route.path.startsWith('/vendor/monitor'))
const activeScreen = computed(() => Number(route.query.screen) || 1)

const emit = defineEmits<{
  (e: 'search', query: string): void
}>()

function switchMonitorTab(tab: number) {
  router.replace({
    query: { ...route.query, screen: tab.toString() },
  })
}

function handleLogout() {
  return logoutAndRedirect(router)
}
</script>

<template>
  <header class="w-full bg-white border-b border-slate-100 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
      <!-- Brand & Navigation Tabs -->
      <div class="flex items-center gap-8">
        <router-link to="/" aria-label="Beranda Anteraja Pharma" class="inline-flex shrink-0 items-center">
          <img
            alt="Anteraja Pharma"
            class="h-9 w-auto object-contain"
            src="/pharma-logo.webp"
          />
        </router-link>

        <!-- Monitor Tabs (when on vendor monitor page) -->
        <nav v-if="isMonitorPage" class="flex items-center gap-2">
          <button
            type="button"
            @click="switchMonitorTab(1)"
            class="font-semibold px-4 py-1.5 rounded-full text-sm transition-all cursor-pointer"
            :class="activeScreen === 1
              ? 'bg-[#FCE7F3] text-[#D81E5B]'
              : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
          >
            Halaman Pantau
          </button>
          <button
            type="button"
            @click="switchMonitorTab(2)"
            class="px-3.5 py-1.5 rounded-full text-sm transition-all cursor-pointer"
            :class="activeScreen === 2
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold'
              : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
          >
            Pelacakan GPS
          </button>
          <button
            type="button"
            @click="switchMonitorTab(3)"
            class="px-3.5 py-1.5 rounded-full text-sm transition-all cursor-pointer"
            :class="activeScreen === 3
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold'
              : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
          >
            Laporan
          </button>
        </nav>

        <!-- Global Vendor Navigation (when on home or package list) -->
        <nav v-else class="flex items-center gap-2">
          <router-link
            to="/"
            class="font-medium px-4 py-1.5 rounded-full text-sm transition-all"
            :class="route.path === '/' || route.path === '/vendor'
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-xs'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            Beranda
          </router-link>
          <router-link
            to="/paket-saya"
            class="font-medium px-4 py-1.5 rounded-full text-sm transition-all"
            :class="route.path === '/paket-saya'
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-semibold shadow-xs'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            Paket Saya
          </router-link>
        </nav>
      </div>

      <!-- Right: Facility Badge & Actions -->
      <div class="flex items-center gap-3">
        <!-- Right Badge: RS Medika Sehat Jakarta with green dot -->
        <div class="hidden sm:flex items-center gap-2.5 px-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="font-semibold text-slate-800">{{ currentFacility.name }}</span>
          <span class="text-slate-400 font-normal hidden lg:inline">• {{ currentFacility.department }}</span>
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

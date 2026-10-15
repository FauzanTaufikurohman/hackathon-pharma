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
  <header class="sticky top-0 z-50 bg-white border-b border-slate-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 h-20 flex items-center justify-between gap-4">
      <!-- Brand & Navigation Tabs Group -->
      <div class="flex items-center gap-8">
        <router-link to="/" aria-label="Beranda Anteraja Pharma" class="flex items-center shrink-0">
          <img
            alt="Anteraja Pharma"
            class="h-9 w-auto object-contain"
            src="/pharma-logo.webp"
          />
        </router-link>

        <!-- Monitor Tabs (when on vendor monitor page) -->
        <nav v-if="isMonitorPage" class="hidden md:flex items-center gap-2">
          <button
            type="button"
            @click="switchMonitorTab(1)"
            class="px-3.5 py-1.5 rounded-full text-sm transition-all cursor-pointer flex items-center gap-1.5"
            :class="activeScreen === 1
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
              : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
          >
            <span v-if="activeScreen === 1" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Halaman Pantau</span>
          </button>

          <button
            type="button"
            @click="switchMonitorTab(2)"
            class="px-3.5 py-1.5 rounded-full text-sm transition-all cursor-pointer flex items-center gap-1.5"
            :class="activeScreen === 2
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
              : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
          >
            <span v-if="activeScreen === 2" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Pelacakan GPS</span>
          </button>

          <button
            type="button"
            @click="switchMonitorTab(3)"
            class="px-3.5 py-1.5 rounded-full text-sm transition-all cursor-pointer flex items-center gap-1.5"
            :class="activeScreen === 3
              ? 'bg-[#FCE7F3] text-[#D81E5B] font-bold shadow-xs'
              : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-slate-100'"
          >
            <span v-if="activeScreen === 3" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Laporan</span>
          </button>
        </nav>

        <!-- Global Vendor Navigation (when on home or package list) -->
        <nav v-else class="hidden md:flex items-center gap-2">
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

      <!-- Right Side Badges -->
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-left shadow-xs">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="material-symbols-outlined text-emerald-600 text-[18px]">local_hospital</span>
          <div>
            <p class="text-xs font-bold text-slate-800 leading-tight">{{ currentFacility.name }}</p>
            <p class="text-[10px] text-slate-500 leading-none">{{ currentFacility.department }}</p>
          </div>
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

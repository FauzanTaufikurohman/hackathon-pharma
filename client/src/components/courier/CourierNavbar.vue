<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { mockCourierProfile } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

const route = useRoute()
const router = useRouter()

const emit = defineEmits<{
  (e: 'notify-click'): void
}>()

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
          to="/courier"
          class="flex items-center focus:outline-none rounded-lg shrink-0"
          title="Beranda Kurir Anteraja Pharma"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 w-auto object-contain"
            src="/pharma-logo.webp"
          />
        </router-link>

        <!-- Navigation Tabs -->
        <nav class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto py-1">
          <!-- Nav 1: Beranda -->
          <router-link
            to="/courier"
            class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
            :class="route.path === '/courier' || route.path === '/courier/beranda'
              ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs'
              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100'"
          >
            <span v-if="route.path === '/courier' || route.path === '/courier/beranda'" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Beranda</span>
          </router-link>

          <!-- Nav 2: Tugas Masuk & Monitor -->
          <router-link
            to="/courier/tugas/PHA-260104"
            class="transition-all py-1.5 px-3 sm:px-3.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
            :class="route.path.startsWith('/courier/tugas')
              ? 'bg-[#FCE7F3] text-[#D81E5B] shadow-xs'
              : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100'"
          >
            <span v-if="route.path.startsWith('/courier/tugas')" class="w-1.5 h-1.5 rounded-full bg-[#D81E5B]"></span>
            <span>Tugas Masuk</span>
          </router-link>
        </nav>
      </div>

      <!-- Right: Courier Profile Chip & Logout -->
      <div class="flex items-center gap-3 shrink-0">
        <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200/80 shadow-xs">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="font-semibold text-xs text-slate-800">{{ mockCourierProfile.name }} ({{ mockCourierProfile.role_label }})</span>
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

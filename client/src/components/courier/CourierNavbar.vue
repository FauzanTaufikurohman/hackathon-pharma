<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { mockCourierProfile } from '../../data/mockShipments'
import { logoutAndRedirect } from '../../services/auth'

const route = useRoute()
const router = useRouter()
const showNotifications = ref(false)

const emit = defineEmits<{
  (e: 'notify-click'): void
}>()

function handleLogout() {
  return logoutAndRedirect(router)
}
</script>

<template>
  <header class="fixed top-0 left-0 w-full z-50 bg-white border-b border-slate-100 shadow-xs">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
      <!-- Left: Logo & Role Pill -->
      <div class="flex items-center gap-3 shrink-0">
        <router-link to="/courier" class="flex items-center gap-2 group">
          <span class="font-headline-md font-bold tracking-tight text-on-surface flex items-center gap-1.5">
            <span class="text-primary-container font-extrabold group-hover:scale-105 transition-transform">anteraja</span>
            <span class="font-medium text-slate-700">pharma</span>
          </span>
          <span class="px-2 py-0.5 rounded-full border border-pink-200 bg-pink-50 text-[10px] font-bold text-primary-container tracking-wider uppercase">
            Satria Pharma
          </span>
        </router-link>
      </div>

      <!-- Center: 2 Navigations (Beranda & Tugas Saya / Kurir) -->
      <nav class="hidden md:flex items-center p-1 bg-slate-50/80 rounded-full border border-slate-100">
        <!-- Nav 1: Beranda -->
        <router-link
          to="/courier"
          class="transition-all px-6 py-2.5 rounded-full text-label-md font-semibold cursor-pointer"
          :class="route.path === '/courier' || route.path === '/courier/beranda'
            ? 'bg-[#FCE7F3] text-primary-container font-bold shadow-2xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
        >
          Beranda
        </router-link>

        <!-- Nav 2: Tugas Saya -->
        <router-link
          to="/courier/tugas-saya"
          class="transition-all px-6 py-2.5 rounded-full text-label-md font-semibold cursor-pointer"
          :class="route.path.startsWith('/courier/tugas-saya')
            ? 'bg-[#FCE7F3] text-primary-container font-bold shadow-2xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
        >
          Tugas Saya
        </router-link>
      </nav>

      <!-- Right: Courier Duty Chip & Profile -->
      <div class="flex items-center gap-4 shrink-0">
        <!-- Duty Status Tag -->
        <div class="flex items-center gap-2.5 py-1 px-3 rounded-full bg-slate-50 border border-slate-100 shadow-2xs">
          <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary"></span>
          </span>
          <span class="font-label-md text-slate-700 font-medium hidden sm:inline-block">
            {{ mockCourierProfile.name }} <span class="text-slate-400 font-normal">• {{ mockCourierProfile.role_label }}</span>
          </span>
        </div>

        <!-- Notification Bell -->
        <button
          type="button"
          aria-label="Notifications"
          @click="emit('notify-click')"
          class="relative p-2 text-slate-500 hover:text-slate-800 rounded-full hover:bg-slate-100 transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined text-[20px]">notifications</span>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary-container"></span>
        </button>

        <!-- Profile Avatar -->
        <img
          alt="Profile Satria"
          class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200 shadow-2xs"
          src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120"
        />

        <button
          type="button"
          @click="handleLogout"
          class="font-label-md text-slate-500 hover:text-primary-container transition-colors pl-1 cursor-pointer font-medium"
        >
          Keluar
        </button>
      </div>
    </div>
  </header>
</template>

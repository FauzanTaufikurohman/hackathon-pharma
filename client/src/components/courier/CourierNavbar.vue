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
  <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-surface-container/40">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-space-md">
      <!-- Left: Logo, Role Badge & Navigation Tabs -->
      <div class="flex items-center gap-space-md lg:gap-space-lg">
        <!-- Brand Logo -->
        <router-link
          to="/courier"
          class="flex items-center gap-space-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg group shrink-0"
          title="Portal Satria Pharma"
        >
          <img
            alt="Anteraja Pharma"
            class="h-9 md:h-10 w-auto object-contain transition-transform group-hover:scale-105"
            src="/images/anteraja-pharma-logo.png"
          />
        </router-link>

        <!-- Role Badge -->
        <span class="hidden sm:inline-flex bg-inverse-surface text-inverse-on-surface font-label-sm text-label-sm px-2.5 py-1 rounded-full uppercase tracking-widest items-center gap-1.5 shadow-sm font-bold">
          <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim"></span>
          SATRIA PHARMA
        </span>

        <!-- Navigation Tabs -->
        <nav class="flex items-center gap-space-xs sm:gap-space-sm pl-2">
          <!-- Nav 1: Beranda -->
          <router-link
            to="/courier"
            class="font-label-lg text-label-lg transition-colors py-2 px-3.5 sm:px-4 rounded-full"
            :class="route.path === '/courier' || route.path === '/courier/beranda'
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Beranda
          </router-link>

          <!-- Nav 2: Tugas Saya -->
          <router-link
            to="/courier/tugas-saya"
            class="font-label-lg text-label-lg transition-colors py-2 px-3.5 sm:px-4 rounded-full"
            :class="route.path.startsWith('/courier/tugas')
              ? 'text-primary font-bold bg-primary-fixed/40 shadow-2xs'
              : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low'"
          >
            Tugas Saya
          </router-link>
        </nav>
      </div>

      <!-- Right: Courier Duty Chip, Notification & Profile -->
      <div class="flex items-center gap-3 sm:gap-4 shrink-0">
        <!-- Duty Status Tag -->
        <div class="flex items-center gap-2 py-1.5 px-3 rounded-full bg-surface-container-low border border-surface-container shadow-2xs">
          <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary"></span>
          </span>
          <span class="font-label-md text-on-surface font-bold hidden sm:inline-block">
            {{ mockCourierProfile.name }}
            <span class="text-on-surface-variant font-normal">• {{ mockCourierProfile.role_label }}</span>
          </span>
        </div>

        <!-- Notification Bell -->
        <button
          type="button"
          aria-label="Notifications"
          @click="emit('notify-click')"
          class="relative p-2 text-on-surface-variant hover:text-on-surface rounded-full hover:bg-surface-container transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined text-[20px]">notifications</span>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary animate-pulse"></span>
        </button>

        <!-- Profile Avatar -->
        <div class="relative shrink-0">
          <img
            alt="Profile Satria"
            class="w-9 h-9 rounded-full object-cover ring-2 ring-primary/20 shadow-xs"
            src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120"
          />
        </div>
        <!-- Role Switcher -->
        <div class="hidden lg:flex items-center gap-1.5 pl-2 border-l border-surface-container-high">
          <router-link
            to="/"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
            title="Beralih ke Portal Faskes"
          >
            <span class="material-symbols-outlined text-[14px]">local_hospital</span>
            <span>Faskes</span>
          </router-link>
          <router-link
            to="/admin"
            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-primary font-label-sm text-[11px] font-semibold transition-all border border-surface-container"
            title="Beralih ke Portal Admin"
          >
            <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
            <span>Admin</span>
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>
